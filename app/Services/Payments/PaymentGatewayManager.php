<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;
use InvalidArgumentException;
use RuntimeException;

class PaymentGatewayManager
{
    public function resolve(string $provider): PaymentGateway
    {
        $gatewayClass = config("payments.gateways.{$provider}");

        if (! is_string($gatewayClass)) {
            throw new InvalidArgumentException("Unsupported payment provider [{$provider}].");
        }

        $gateway = app($gatewayClass);

        if (! $gateway instanceof PaymentGateway) {
            throw new RuntimeException("Payment gateway [{$gatewayClass}] must implement PaymentGateway.");
        }

        return $gateway;
    }
}
