<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Affiliate>
 */
class AffiliateFactory extends Factory
{
    protected $model = Affiliate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'code' => Str::upper(fake()->bothify('AFF###??')),
            'commission_type' => Affiliate::COMMISSION_TYPE_PERCENTAGE,
            'commission_value' => fake()->numberBetween(5, 20),
            'cookie_days' => 30,
            'is_active' => true,
            'total_earnings' => 0,
        ];
    }

    public function fixedCommission(): static
    {
        return $this->state(fn () => [
            'commission_type' => Affiliate::COMMISSION_TYPE_FIXED,
            'commission_value' => fake()->numberBetween(200, 5000),
        ]);
    }
}
