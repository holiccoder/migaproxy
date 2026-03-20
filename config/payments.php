<?php

return [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'fake'),

    'enable_order' => filter_var(env('ENABLE_ORDER', false), FILTER_VALIDATE_BOOLEAN),

    'gateways' => [
        'fake' => App\Services\Payments\Gateways\FakePaymentGateway::class,
    ],
];
