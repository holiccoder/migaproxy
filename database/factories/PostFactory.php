<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'excerpt' => fake()->optional()->paragraph(),
            'body' => fake()->paragraphs(5, true),
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
            'cover_image_path' => fake()->boolean(30) ? 'covers/'.fake()->slug().'.jpg' : null,
        ];
    }
}
