<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('authenticated user endpoint includes social profile fields', function () {
    $user = User::factory()->create([
        'skype_profile' => 'live:john.doe',
        'telegram_profile' => '@john_doe',
        'facebook_profile' => 'https://facebook.com/john.doe',
        'x_profile' => 'https://x.com/john_doe',
        'youtube_profile' => 'https://youtube.com/@john_doe',
        'instagram_profile' => 'https://instagram.com/john_doe',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/user');

    $response
        ->assertOk()
        ->assertJsonPath('skype_profile', 'live:john.doe')
        ->assertJsonPath('telegram_profile', '@john_doe')
        ->assertJsonPath('facebook_profile', 'https://facebook.com/john.doe')
        ->assertJsonPath('x_profile', 'https://x.com/john_doe')
        ->assertJsonPath('youtube_profile', 'https://youtube.com/@john_doe')
        ->assertJsonPath('instagram_profile', 'https://instagram.com/john_doe');
});

test('profile update stores social profile fields', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/profile', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'skype_profile' => 'live:jane.doe',
        'telegram_profile' => '@jane_doe',
        'facebook_profile' => 'https://facebook.com/jane.doe',
        'x_profile' => 'https://x.com/jane_doe',
        'youtube_profile' => 'https://youtube.com/@jane_doe',
        'instagram_profile' => 'https://instagram.com/jane_doe',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.user.skype_profile', 'live:jane.doe')
        ->assertJsonPath('data.user.telegram_profile', '@jane_doe')
        ->assertJsonPath('data.user.facebook_profile', 'https://facebook.com/jane.doe')
        ->assertJsonPath('data.user.x_profile', 'https://x.com/jane_doe')
        ->assertJsonPath('data.user.youtube_profile', 'https://youtube.com/@jane_doe')
        ->assertJsonPath('data.user.instagram_profile', 'https://instagram.com/jane_doe');

    expect($user->refresh())
        ->name->toBe('Jane Doe')
        ->email->toBe('jane@example.com')
        ->skype_profile->toBe('live:jane.doe')
        ->telegram_profile->toBe('@jane_doe')
        ->facebook_profile->toBe('https://facebook.com/jane.doe')
        ->x_profile->toBe('https://x.com/jane_doe')
        ->youtube_profile->toBe('https://youtube.com/@jane_doe')
        ->instagram_profile->toBe('https://instagram.com/jane_doe');
});
