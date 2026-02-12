<?php

namespace App\Services\Affiliates;

use App\Models\Affiliate;
use App\Models\AffiliateClick;
use App\Models\AffiliateConversion;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AffiliateService
{
    public function resolveAffiliate(?string $code, ?User $buyer = null): ?Affiliate
    {
        if (! is_string($code) || trim($code) === '') {
            return null;
        }

        $normalizedCode = strtoupper(trim($code));

        $affiliate = Affiliate::query()
            ->where('code', $normalizedCode)
            ->first();

        if (! $affiliate) {
            throw new InvalidArgumentException('Affiliate code is invalid.');
        }

        if (! $affiliate->is_active) {
            throw new InvalidArgumentException('Affiliate is not active.');
        }

        if ($buyer && $affiliate->user_id !== null && $affiliate->user_id === $buyer->id) {
            throw new InvalidArgumentException('Self-referral is not allowed.');
        }

        return $affiliate;
    }

    public function trackClick(string $code, Request $request): AffiliateClick
    {
        $affiliate = $this->resolveAffiliate($code);

        if (! $affiliate instanceof Affiliate) {
            throw new InvalidArgumentException('Affiliate code is invalid.');
        }

        return AffiliateClick::query()->create([
            'affiliate_id' => $affiliate->id,
            'code' => $affiliate->code,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
            'landing_url' => $request->fullUrl(),
            'clicked_at' => now(),
        ]);
    }

    public function createConversionFromOrder(Order $order): ?AffiliateConversion
    {
        if ($order->affiliate_id === null) {
            return null;
        }

        $existingConversion = AffiliateConversion::query()
            ->where('order_id', $order->id)
            ->first();

        if ($existingConversion instanceof AffiliateConversion) {
            return $existingConversion;
        }

        $affiliate = Affiliate::query()->find($order->affiliate_id);

        if (! $affiliate || ! $affiliate->is_active) {
            return null;
        }

        if ($affiliate->user_id !== null && $affiliate->user_id === $order->user_id) {
            return null;
        }

        $commissionAmount = $affiliate->calculateCommission($order->total);

        $conversion = AffiliateConversion::query()->create([
            'affiliate_id' => $affiliate->id,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'subscription_id' => $order->subscription_id,
            'amount' => $order->total,
            'commission_amount' => $commissionAmount,
            'status' => AffiliateConversion::STATUS_APPROVED,
            'approved_at' => now(),
            'paid_at' => null,
        ]);

        $affiliate->increment('total_earnings', $commissionAmount);

        return $conversion;
    }
}
