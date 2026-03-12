<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

test('verification link verifies an unverified user email', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $this->getJson($verificationUrl)
        ->assertOk()
        ->assertJsonPath('message', 'Email verified successfully.');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('verification link is rejected when hash is invalid', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1('wrong-email@example.com'),
        ],
    );

    $this->getJson($verificationUrl)
        ->assertForbidden()
        ->assertJsonPath('message', 'Invalid verification link.');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('resend verification endpoint sends verification notification for unverified users', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->postJson('/api/v1/email/verification-notification', [
        'email' => $user->email,
    ])
        ->assertOk()
        ->assertJsonPath('message', 'If the account exists and is not verified, a verification email has been sent.');

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('unverified users cannot access verified middleware routes', function () {
    $user = User::factory()->unverified()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/orders')->assertForbidden();
});
