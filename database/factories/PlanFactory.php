<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'traffic' => fake()->numberBetween(100, 10000),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(9900, 99900),
            'days' => fake()->randomElement([30, 90, 180, 365]),
        ];
    }
}
