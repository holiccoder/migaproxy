<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->bothify('SAVE###')),
            'name' => fake()->words(2, true),
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => fake()->numberBetween(5, 30),
            'is_active' => true,
            'usage_limit' => null,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ];
    }

    public function fixedAmount(): static
    {
        return $this->state(fn () => [
            'type' => Coupon::TYPE_FIXED,
            'value' => fake()->numberBetween(500, 5000),
        ]);
    }
}
