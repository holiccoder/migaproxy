<?php

use App\Models\Ipmart;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('register creates an unverified user and sends a verification email', function () {
    Notification::fake();

    $requestedIpmartEmail = null;

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('registerUser')
        ->once()
        ->andReturnUsing(function (string $email, string $password, string $remark = '') use (&$requestedIpmartEmail): array {
            $requestedIpmartEmail = $email;

            expect($email)->toEndWith('@migaproxy.com');
            expect($email)->not->toBe('jane@example.com');
            expect($password)->toBe('password123');
            expect($remark)->toBe('Jane Doe');

            return [
                'ipmart_id' => '68196e82da94ea3c1c488fd3',
                'ipmart_email' => $email,
                'plan_balance' => '0',
                'proxyName' => '5Vj38Pj',
                'proxyPwd' => 'Mv2Ld2Ij6Ej',
                'login_name' => 'o5Hj8Pj',
                'passwd' => 'benny04asddddlsi',
            ];
        });

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Registration successful. Please verify your email before logging in.')
        ->assertJsonPath('data.user.email', 'jane@example.com')
        ->assertJsonPath('data.user.email_verified', false)
        ->assertJsonPath('data.email_verification_required', true)
        ->assertJsonMissingPath('data.token');

    $userId = (string) $response->json('data.user.id');

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'email_verified_at' => null,
    ]);

    $user = User::query()->find($userId);
    expect($user)->toBeInstanceOf(User::class);

    Notification::assertSentTo($user, VerifyEmail::class);

    expect($requestedIpmartEmail)->not->toBeNull();
    expect($requestedIpmartEmail)->not->toBe('jane@example.com');

    $this->assertDatabaseHas('ipmarts', [
        'user_id' => $userId,
        'ipmart_id' => '68196e82da94ea3c1c488fd3',
        'ipmart_email' => $requestedIpmartEmail,
        'plan_balance' => '0',
        'proxyName' => '5Vj38Pj',
        'proxyPwd' => 'Mv2Ld2Ij6Ej',
        'login_name' => 'o5Hj8Pj',
        'passwd' => 'benny04asddddlsi',
    ]);
});

test('login requires a verified email address', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);

    $this->postJson('/api/v1/login', [
        'email' => 'jane@example.com',
        'password' => 'password123',
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'Please verify your email before logging in.')
        ->assertJsonPath('email_verification_required', true);

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('login returns token for valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-login-1',
        'plan_balance' => '256',
        'proxyName' => 'proxy-user-name',
        'proxyPwd' => 'proxy-user-password',
        'login_name' => 'login-user-name',
        'passwd' => 'ipmart-password',
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'jane@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Login successful.')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email_verified', true)
        ->assertJsonPath('data.user.ipmart.ipmart_id', 'ipmart-login-1')
        ->assertJsonPath('data.user.ipmart.plan_balance', 256)
        ->assertJsonPath('data.user.ipmart.proxyName', 'proxy-user-name')
        ->assertJsonPath('data.user.ipmart.proxyPwd', 'proxy-user-password')
        ->assertJsonMissingPath('data.user.plan_balance')
        ->assertJsonMissingPath('data.user.proxyName')
        ->assertJsonMissingPath('data.user.proxyPwd')
        ->assertJsonPath('data.token_type', 'Bearer');
});

test('login fails for invalid credentials', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'jane@example.com',
        'password' => 'wrong-password',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Invalid credentials.');
});
