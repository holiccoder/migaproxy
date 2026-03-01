<?php

namespace Database\Factories;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HelpArticle>
 */
class HelpArticleFactory extends Factory
{
    protected $model = HelpArticle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'help_category_id' => HelpCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->sentence(12),
            'related_slugs' => [],
            'is_published' => true,
        ];
    }
}
