<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\PaymentGateway;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FakePaymentGateway implements PaymentGateway
{
    public function createCheckoutSession(User $user, Plan $plan, Order $order): array
    {
        $checkoutUrl = sprintf(
            '%s/fake-checkout/%s',
            rtrim((string) config('app.url'), '/'),
            $order->public_id
        );

        return [
            'checkout_url' => $checkoutUrl,
            'provider_reference' => 'fake_checkout_'.Str::lower(Str::random(16)),
        ];
    }

    public function parseWebhook(array $payload): array
    {
        $event = $payload['event'] ?? null;

        if (! is_string($event) || $event === '') {
            throw new InvalidArgumentException('Webhook payload requires a valid event.');
        }

        return [
            'event' => $event,
            'order_public_id' => is_string($payload['order_public_id'] ?? null) ? $payload['order_public_id'] : null,
            'provider_reference' => is_string($payload['provider_reference'] ?? null) ? $payload['provider_reference'] : null,
            'subscription_reference' => is_string($payload['subscription_reference'] ?? null) ? $payload['subscription_reference'] : null,
        ];
    }
}
