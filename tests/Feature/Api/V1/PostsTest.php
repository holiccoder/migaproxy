<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;

test('posts index returns only published posts', function () {
    $published = Post::factory()->create([
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subDay(),
    ]);
    $draft = Post::factory()->create([
        'status' => Post::STATUS_DRAFT,
        'published_at' => null,
    ]);

    $response = $this->getJson('/api/v1/posts');

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)
        ->toContain($published->slug)
        ->not->toContain($draft->slug);
});

test('posts index can be filtered by category', function () {
    $category = Category::factory()->create();
    $otherCategory = Category::factory()->create();

    $matching = Post::factory()->create([
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subDays(2),
    ]);
    $matching->categories()->attach($category);

    $nonMatching = Post::factory()->create([
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subDays(3),
    ]);
    $nonMatching->categories()->attach($otherCategory);

    $response = $this->getJson("/api/v1/posts?category={$category->slug}");

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)
        ->toContain($matching->slug)
        ->not->toContain($nonMatching->slug);
});

test('posts index can be filtered by tag', function () {
    $tag = Tag::factory()->create();
    $otherTag = Tag::factory()->create();

    $matching = Post::factory()->create([
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subDays(2),
    ]);
    $matching->tags()->attach($tag);

    $nonMatching = Post::factory()->create([
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subDays(3),
    ]);
    $nonMatching->tags()->attach($otherTag);

    $response = $this->getJson("/api/v1/posts?tag={$tag->slug}");

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)
        ->toContain($matching->slug)
        ->not->toContain($nonMatching->slug);
});

test('posts show returns a published post by slug', function () {
    $post = Post::factory()->create([
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->getJson("/api/v1/posts/{$post->slug}");

    $response
        ->assertOk()
        ->assertJsonPath('data.slug', $post->slug);
});

test('posts show rejects unpublished posts', function () {
    $draft = Post::factory()->create([
        'status' => Post::STATUS_DRAFT,
        'published_at' => null,
    ]);
    $scheduled = Post::factory()->create([
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->addDay(),
    ]);

    $this->getJson("/api/v1/posts/{$draft->slug}")->assertNotFound();
    $this->getJson("/api/v1/posts/{$scheduled->slug}")->assertNotFound();
});
