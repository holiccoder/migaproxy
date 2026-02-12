<?php

namespace App\Contracts\Payments;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;

interface PaymentGateway
{
    /**
     * @return array{checkout_url: string, provider_reference: string}
     */
    public function createCheckoutSession(User $user, Plan $plan, Order $order): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     event: string,
     *     order_public_id: string|null,
     *     provider_reference: string|null,
     *     subscription_reference: string|null
     * }
     */
    public function parseWebhook(array $payload): array;
}
