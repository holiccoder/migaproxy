<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::query()->count() > 0
            ? Category::all()
            : Category::factory()->count(6)->create();

        $tags = Tag::query()->count() > 0
            ? Tag::all()
            : Tag::factory()->count(12)->create();

        Post::factory()
            ->count(15)
            ->create()
            ->each(function (Post $post) use ($categories, $tags): void {
                $post->categories()->attach(
                    $categories->random(fake()->numberBetween(1, 3))->modelKeys()
                );

                $post->tags()->attach(
                    $tags->random(fake()->numberBetween(2, 5))->modelKeys()
                );
            });
    }
}
