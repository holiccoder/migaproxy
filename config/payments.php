<?php

return [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'fake'),

    'gateways' => [
        'fake' => App\Services\Payments\Gateways\FakePaymentGateway::class,
    ],
];
