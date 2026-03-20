<?php

namespace App\Services\Payments;

use App\Http\Controllers\Api\V1\IPmartController;
use App\Models\BalanceHistory;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Affiliates\AffiliateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        public PaymentGatewayManager $gatewayManager,
        public AffiliateService $affiliateService,
    ) {}

    public function createCheckout(User $user, Plan $plan, string $provider, ?string $couponCode = null, ?string $affiliateCode = null): Order
    {
        $coupon = $this->resolveCoupon($couponCode);
        $affiliate = $this->affiliateService->resolveAffiliate($affiliateCode, $user);
        $subtotal = $plan->price;
        $discountTotal = $coupon?->calculateDiscount($subtotal) ?? 0;
        $total = max(0, $subtotal - $discountTotal);

        $order = Order::query()->create([
            'public_id' => Str::ulid()->toBase32(),
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'coupon_id' => $coupon?->id,
            'affiliate_id' => $affiliate?->id,
            'coupon_code' => $coupon?->code,
            'affiliate_code' => $affiliate?->code,
            'subscription_id' => null,
            'status' => Order::STATUS_PENDING,
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'total' => $total,
            'currency' => 'USD',
            'metadata' => [],
            'paid_at' => null,
            'failed_at' => null,
        ]);

        $session = $this->gatewayManager->resolve($provider)->createCheckoutSession($user, $plan, $order);

        $order->forceFill([
            'metadata' => array_merge($order->metadata ?? [], [
                'provider' => $provider,
                'provider_reference' => $session['provider_reference'],
                'checkout_url' => $session['checkout_url'],
            ]),
        ])->save();

        return $order->fresh(['plan', 'user', 'coupon', 'affiliate']);
    }

    /**
     * @return array{order: Order, subscription: Subscription|null, wallet_balance: int}
     */
    public function purchaseWithWallet(
        User $user,
        Plan $plan,
        string $paymentMethod = 'wallet',
        ?string $couponCode = null,
        ?string $affiliateCode = null,
        ?string $orderComment = null,
    ): array {
        if ($paymentMethod !== 'wallet') {
            throw new InvalidArgumentException('Only wallet payment is currently supported.');
        }

        return DB::transaction(function () use ($user, $plan, $paymentMethod, $couponCode, $affiliateCode, $orderComment): array {
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedUser) {
                throw new InvalidArgumentException('User account is not available.');
            }

            $coupon = $this->resolveCoupon($couponCode);
            $affiliate = $this->affiliateService->resolveAffiliate($affiliateCode, $lockedUser);
            $subtotal = $plan->price;
            $discountTotal = $coupon?->calculateDiscount($subtotal) ?? 0;
            $total = max(0, $subtotal - $discountTotal);

            $beforeBalance = (int) $lockedUser->balance;

            if ($beforeBalance < $total) {
                throw new InvalidArgumentException('Insufficient wallet balance.');
            }

            $afterBalance = $beforeBalance - $total;
            $trimmedOrderComment = is_string($orderComment) ? trim($orderComment) : '';
            $orderMetadata = [
                'payment_method' => $paymentMethod,
            ];

            if ($trimmedOrderComment !== '') {
                $orderMetadata['order_comment'] = $trimmedOrderComment;
            }

            $order = Order::query()->create([
                'public_id' => Str::ulid()->toBase32(),
                'user_id' => $lockedUser->id,
                'plan_id' => $plan->id,
                'coupon_id' => $coupon?->id,
                'affiliate_id' => $affiliate?->id,
                'coupon_code' => $coupon?->code,
                'affiliate_code' => $affiliate?->code,
                'subscription_id' => null,
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'total' => $total,
                'currency' => 'USD',
                'metadata' => $orderMetadata,
                'paid_at' => null,
                'failed_at' => null,
            ]);

            $lockedUser->forceFill([
                'balance' => $afterBalance,
            ])->save();

            $lockedUser->balanceHistories()->create([
                'type' => BalanceHistory::TYPE_DEBIT,
                'amount' => $total * -1,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'reference' => $order->public_id,
                'description' => $trimmedOrderComment !== ''
                    ? $trimmedOrderComment
                    : sprintf('Wallet payment for order %s.', $order->public_id),
            ]);

            $this->markOrderPaid($order, null, null);

            $order->refresh();
            $order->load(['plan', 'user', 'coupon', 'affiliate', 'subscription']);

            return [
                'order' => $order,
                'subscription' => $order->subscription,
                'wallet_balance' => $afterBalance,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(string $provider, array $payload): void
    {
        $parsedPayload = $this->gatewayManager->resolve($provider)->parseWebhook($payload);
        $orderPublicId = $parsedPayload['order_public_id'];

        if (! is_string($orderPublicId) || $orderPublicId === '') {
            return;
        }

        $order = Order::query()
            ->with(['plan', 'affiliate', 'user.ipmart'])
            ->where('public_id', $orderPublicId)
            ->first();

        if (! $order) {
            return;
        }

        if (in_array($parsedPayload['event'], ['checkout.completed', 'invoice.payment_succeeded'], true)) {
            $this->markOrderPaid($order, $parsedPayload['provider_reference'], $parsedPayload['subscription_reference']);

            return;
        }

        if ($parsedPayload['event'] === 'invoice.payment_failed') {
            $order->forceFill([
                'status' => Order::STATUS_FAILED,
                'failed_at' => now(),
            ])->save();

            Subscription::query()
                ->where('order_id', $order->id)
                ->update([
                    'status' => Subscription::STATUS_PAST_DUE,
                ]);

            return;
        }

        if ($parsedPayload['event'] === 'customer.subscription.deleted') {
            $this->cancelSubscription($order, $parsedPayload['subscription_reference']);
        }
    }

    private function markOrderPaid(Order $order, ?string $providerReference, ?string $subscriptionReference): void
    {
        $wasUnpaid = $order->paid_at === null;

        if ($this->isOrderEnabled() && $wasUnpaid) {
            $order->loadMissing('plan');

            $ipmartOrder = $this->orderForCustomer(
                (int) $order->user_id,
                (int) ($order->plan?->traffic ?? 0),
            );

            if (is_array($ipmartOrder)) {
                $order->forceFill([
                    'ipmart_order' => $ipmartOrder,
                ])->save();
            }
        }

        $order->forceFill([
            'status' => Order::STATUS_PAID,
            'paid_at' => now(),
            'failed_at' => null,
        ])->save();

        if ($wasUnpaid && $order->coupon_id !== null) {
            Coupon::query()
                ->where('id', $order->coupon_id)
                ->increment('used_count');
        }

        $subscription = Subscription::query()
            ->where('user_id', $order->user_id)
            ->where('plan_id', $order->plan_id)
            ->latest('id')
            ->first();

        if (! $subscription) {
            $subscription = new Subscription;
        }

        $startsAt = now();
        $endsAt = now()->addDays($order->plan->durationInDays());

        $subscription->fill([
            'user_id' => $order->user_id,
            'plan_id' => $order->plan_id,
            'order_id' => $order->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'canceled_at' => null,
        ]);
        $subscription->save();

        $order->forceFill([
            'subscription_id' => $subscription->id,
        ])->save();

        if ($wasUnpaid) {
            $this->affiliateService->createConversionFromOrder($order);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function orderForCustomer(int $userId, int $amount): ?array
    {
        if ($amount <= 0) {
            return null;
        }

        try {
            $response = app(IPmartController::class)->orderForCustomer($userId, $amount);
        } catch (\Throwable $throwable) {
            Log::warning('IPmart order-for-customer call threw an exception.', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }

        $payload = $response->getData(true);

        if ($response->getStatusCode() >= 400 || ! is_array($payload) || ! (bool) ($payload['success'] ?? false)) {
            Log::warning('IPmart order-for-customer call returned an unsuccessful response.', [
                'user_id' => $userId,
                'amount' => $amount,
                'status_code' => $response->getStatusCode(),
                'payload' => $payload,
            ]);

            return null;
        }

        $ipmartOrder = data_get($payload, 'data.order');

        if (! is_array($ipmartOrder)) {
            return null;
        }

        return $ipmartOrder;
    }

    private function isOrderEnabled(): bool
    {
        return (bool) config('payments.enable_order', false);
    }

    private function cancelSubscription(Order $order, ?string $subscriptionReference): void
    {
        $subscription = Subscription::query()
            ->where('user_id', $order->user_id)
            ->where('order_id', $order->id)
            ->latest('id')
            ->first();

        if (! $subscription) {
            return;
        }

        $subscription->forceFill([
            'status' => Subscription::STATUS_CANCELED,
            'canceled_at' => now(),
        ])->save();
    }

    private function resolveCoupon(?string $couponCode): ?Coupon
    {
        if (! is_string($couponCode) || trim($couponCode) === '') {
            return null;
        }

        $normalizedCode = strtoupper(trim($couponCode));

        $coupon = Coupon::query()
            ->where('code', $normalizedCode)
            ->first();

        if (! $coupon) {
            throw new InvalidArgumentException('Coupon code is invalid.');
        }

        if (! $coupon->isAvailable()) {
            throw new InvalidArgumentException('Coupon is not available.');
        }

        return $coupon;
    }
}
