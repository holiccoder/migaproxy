<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => Str::ulid()->toBase32(),
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'coupon_id' => null,
            'affiliate_id' => null,
            'coupon_code' => null,
            'affiliate_code' => null,
            'subscription_id' => null,
            'status' => Order::STATUS_PENDING,
            'subtotal' => fake()->numberBetween(9900, 99900),
            'discount_total' => 0,
            'total' => fake()->numberBetween(9900, 99900),
            'currency' => 'USD',
            'metadata' => [],
            'ipmart_order' => null,
            'paid_at' => null,
            'failed_at' => null,
        ];
    }
}
