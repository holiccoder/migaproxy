<?php

namespace Database\Factories;

use App\Models\HelpCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HelpCategory>
 */
class HelpCategoryFactory extends Factory
{
    protected $model = HelpCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);

        return [
            'title' => Str::title($title),
            'slug' => Str::slug($title),
            'icon' => fake()->randomElement(['rocket', 'link', 'shield', 'wallet']),
            'description' => fake()->sentence(),
        ];
    }
}
