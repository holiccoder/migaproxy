<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckoutRequest;
use App\Models\Plan;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function store(CheckoutRequest $request, PaymentService $paymentService): JsonResponse
    {
        $payload = $request->validated();
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $plan = Plan::query()->findOrFail($payload['plan_id']);
        $paymentMethod = isset($payload['payment_method']) && is_string($payload['payment_method'])
            ? $payload['payment_method']
            : 'wallet';

        try {
            $checkout = $paymentService->purchaseWithWallet(
                user: $user,
                plan: $plan,
                paymentMethod: $paymentMethod,
                couponCode: $payload['coupon_code'] ?? null,
                affiliateCode: $payload['affiliate_code'] ?? null,
                orderComment: $payload['order_comment'] ?? null,
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Payment successful. Plan ordered and subscription activated.',
            'data' => [
                'order' => $checkout['order'],
                'subscription' => $checkout['subscription'],
                'wallet' => [
                    'balance' => $checkout['wallet_balance'],
                    'balance_formatted' => number_format($checkout['wallet_balance'] / 100, 2, '.', ''),
                ],
            ],
        ], 201);
    }
}
