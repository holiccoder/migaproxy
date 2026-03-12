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
        $provider = $payload['provider'] ?? (string) config('payments.default_gateway');

        try {
            $order = $paymentService->createCheckout(
                user: $user,
                plan: $plan,
                provider: $provider,
                couponCode: $payload['coupon_code'] ?? null,
                affiliateCode: $payload['affiliate_code'] ?? null,
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Checkout session created successfully.',
            'data' => [
                'order' => $order,
                'checkout_url' => $order->metadata['checkout_url'] ?? null,
            ],
        ], 201);
    }
}
