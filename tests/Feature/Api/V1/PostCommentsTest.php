<?php

use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('post comments index returns comments for a published post', function () {
    $post = Post::factory()->create([
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subDay(),
    ]);
    $comment = PostComment::factory()->create([
        'post_id' => $post->id,
        'content' => 'Great article!',
    ]);

    $response = $this->getJson("/api/v1/posts/{$post->slug}/comments");

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $comment->id)
        ->assertJsonPath('data.0.content', 'Great article!');
});

test('posting a comment requires authentication', function () {
    $post = Post::factory()->create([
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subDay(),
    ]);

    $this->postJson("/api/v1/posts/{$post->slug}/comments", [
        'content' => 'Nice one.',
    ])->assertUnauthorized();
});

test('authenticated user can post comment', function () {
    $post = Post::factory()->create([
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subDay(),
    ]);
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/posts/{$post->slug}/comments", [
        'content' => 'Very helpful post.',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.content', 'Very helpful post.')
        ->assertJsonPath('data.user.id', $user->id);

    $this->assertDatabaseHas('post_comments', [
        'post_id' => $post->id,
        'user_id' => $user->id,
        'content' => 'Very helpful post.',
    ]);
});
