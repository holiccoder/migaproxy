<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'traffic' => 1024,
                'description' => 'Perfect for beginners and small projects',
                'features' => [
                    '1 GB Traffic',
                    '5 Concurrent Connections',
                    'HTTP & SOCKS5 Protocols',
                    'Basic Support',
                    '100+ Countries',
                ],
                'price' => 1999,
                'days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'traffic' => 5120,
                'description' => 'Best for professionals and businesses',
                'features' => [
                    '5 GB Traffic',
                    '20 Concurrent Connections',
                    'HTTP & SOCKS5 Protocols',
                    'Priority Support',
                    '150+ Countries',
                    'Auto IP Rotation',
                ],
                'price' => 4999,
                'days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Business',
                'traffic' => 20480,
                'description' => 'Ideal for businesses with high demands',
                'features' => [
                    '20 GB Traffic',
                    '50 Concurrent Connections',
                    'HTTP & SOCKS5 Protocols',
                    '24/7 Priority Support',
                    '200+ Countries',
                    'Auto IP Rotation',
                    'Dedicated Account Manager',
                ],
                'price' => 9999,
                'days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'traffic' => 102400,
                'description' => 'Maximum performance for enterprise needs',
                'features' => [
                    '100 GB Traffic',
                    'Unlimited Concurrent Connections',
                    'HTTP & SOCKS5 Protocols',
                    '24/7 Dedicated Support',
                    'All Countries',
                    'Auto IP Rotation',
                    'Custom Solutions',
                    'API Access',
                ],
                'price' => 29999,
                'days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Annual Pro',
                'traffic' => 102400,
                'description' => 'Pro plan with annual billing - save 20%',
                'features' => [
                    '100 GB Traffic',
                    '20 Concurrent Connections',
                    'HTTP & SOCKS5 Protocols',
                    'Priority Support',
                    '150+ Countries',
                    'Auto IP Rotation',
                ],
                'price' => 47999,
                'days' => 365,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->create($plan);
        }
    }
}
