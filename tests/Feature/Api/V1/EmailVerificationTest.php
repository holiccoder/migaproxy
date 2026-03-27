<?php

use App\Models\Affiliate;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

test('verification link verifies an unverified user email and creates ipmart and affiliate accounts', function () {
    $user = User::factory()->unverified()->create();

    $this->assertDatabaseMissing('affiliates', [
        'user_id' => $user->id,
    ]);

    $requestedIpmartEmail = null;

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('registerUser')
        ->once()
        ->andReturnUsing(function (string $email, string $password, string $remark = '') use (&$requestedIpmartEmail, $user): array {
            $requestedIpmartEmail = $email;

            expect($email)->toEndWith('@migaproxy.com');
            expect($password)->toBeString()->toHaveLength(16);
            expect($remark)->toBe($user->name);

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

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $response = $this->getJson($verificationUrl)
        ->assertSuccessful()
        ->assertJsonPath('message', 'Email verified successfully.')
        ->assertJsonPath('data.user.email_verified', true)
        ->assertJsonPath('data.token_type', 'Bearer');

    expect($response->json('data.token'))->toBeString()->not->toBe('');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    expect($requestedIpmartEmail)->not->toBeNull();

    $this->assertDatabaseHas('ipmarts', [
        'user_id' => (string) $user->id,
        'ipmart_id' => '68196e82da94ea3c1c488fd3',
        'ipmart_email' => $requestedIpmartEmail,
        'plan_balance' => '0',
        'proxyName' => '5Vj38Pj',
        'proxyPwd' => 'Mv2Ld2Ij6Ej',
        'login_name' => 'o5Hj8Pj',
        'passwd' => 'benny04asddddlsi',
    ]);

    $this->assertDatabaseHas('affiliates', [
        'user_id' => $user->id,
        'name' => $user->name.' Affiliate',
        'code' => 'AFFU'.$user->id,
        'commission_type' => Affiliate::COMMISSION_TYPE_PERCENTAGE,
        'commission_value' => 10,
        'cookie_days' => 30,
        'is_active' => 1,
        'total_earnings' => 0,
    ]);
});

test('verification link uses configured default affiliate commission rate', function () {
    SystemSetting::factory()
        ->defaultAffiliateCommissionRate(18)
        ->create();

    $user = User::factory()->unverified()->create();

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('registerUser')
        ->once()
        ->andReturn([
            'ipmart_id' => '68196e82da94ea3c1c488fd4',
            'ipmart_email' => 'demo@migaproxy.com',
            'plan_balance' => '0',
            'proxyName' => 'ProxyName',
            'proxyPwd' => 'ProxyPassword',
            'login_name' => 'login_name',
            'passwd' => 'password',
        ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $this->getJson($verificationUrl)
        ->assertSuccessful();

    $this->assertDatabaseHas('affiliates', [
        'user_id' => $user->id,
        'commission_type' => Affiliate::COMMISSION_TYPE_PERCENTAGE,
        'commission_value' => 18,
    ]);
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

test('verification notification links to frontend email verification page with signed verification parameters', function () {
    Notification::fake();

    config()->set('app.frontend_url', 'https://frontend.example.com');

    $user = User::factory()->unverified()->create();

    $user->sendEmailVerificationNotification();

    Notification::assertSentTo($user, VerifyEmail::class, function (VerifyEmail $notification) use ($user): bool {
        $verificationUrl = $notification->toMail($user)->actionUrl;
        $parsedUrl = parse_url($verificationUrl);

        expect($parsedUrl['scheme'] ?? null)->toBe('https');
        expect($parsedUrl['host'] ?? null)->toBe('frontend.example.com');
        expect($parsedUrl['path'] ?? null)->toBe('/email-verification');

        parse_str($parsedUrl['query'] ?? '', $queryParameters);

        expect($queryParameters['id'] ?? null)->toBe((string) $user->id);
        expect($queryParameters['hash'] ?? null)->toBe(sha1($user->getEmailForVerification()));
        expect($queryParameters['expires'] ?? null)->not->toBeNull();
        expect($queryParameters['signature'] ?? null)->not->toBeNull();

        return true;
    });
});

test('unverified users cannot access verified middleware routes', function () {
    $user = User::factory()->unverified()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/orders')->assertForbidden();
});
