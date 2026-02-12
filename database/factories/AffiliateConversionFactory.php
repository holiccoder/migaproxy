<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\AffiliateConversion;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AffiliateConversion>
 */
class AffiliateConversionFactory extends Factory
{
    protected $model = AffiliateConversion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'subscription_id' => null,
            'amount' => fake()->numberBetween(1000, 50000),
            'commission_amount' => fake()->numberBetween(100, 5000),
            'status' => AffiliateConversion::STATUS_APPROVED,
            'approved_at' => now(),
            'paid_at' => null,
        ];
    }
}
