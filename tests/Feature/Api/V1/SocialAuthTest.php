<?php

use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

test('github callback creates a new user and returns sanctum token', function () {
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('github-123');
    $socialiteUser->shouldReceive('getEmail')->andReturn('octocat@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Octo Cat');
    $socialiteUser->shouldReceive('getNickname')->andReturn('octocat');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('github')
        ->andReturn($provider);

    $response = $this->getJson('/api/v1/auth/github/callback');

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Login successful.')
        ->assertJsonPath('data.user.email', 'octocat@example.com')
        ->assertJsonPath('data.user.github_id', 'github-123')
        ->assertJsonPath('data.token_type', 'Bearer');

    $user = User::query()->where('email', 'octocat@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user?->last_login_at)->not->toBeNull();
    expect($response->json('data.user.last_login_at'))->not->toBeNull();

    $this->assertDatabaseHas('users', [
        'email' => 'octocat@example.com',
        'github_id' => 'github-123',
    ]);
});

test('github callback links existing user by email', function () {
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
        'github_id' => null,
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('github-999');
    $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
    $socialiteUser->shouldReceive('getNickname')->andReturn('existing');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('github')
        ->andReturn($provider);

    $response = $this->getJson('/api/v1/auth/github/callback');

    $response
        ->assertOk()
        ->assertJsonPath('data.user.id', $existingUser->id)
        ->assertJsonPath('data.user.github_id', 'github-999');

    $existingUser->refresh();

    expect($existingUser->last_login_at)->not->toBeNull();
    expect($response->json('data.user.last_login_at'))->not->toBeNull();

    $this->assertDatabaseHas('users', [
        'id' => $existingUser->id,
        'email' => 'existing@example.com',
        'github_id' => 'github-999',
    ]);
});

test('github callback returns validation error on provider failure', function () {
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andThrow(new RuntimeException('OAuth failed'));

    Socialite::shouldReceive('driver')
        ->with('github')
        ->andReturn($provider);

    $this->getJson('/api/v1/auth/github/callback')
        ->assertUnprocessable()
        ->assertJsonPath('message', 'GitHub authentication failed.');
});
