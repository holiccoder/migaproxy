<?php

use App\Models\Ipmart;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('change proxy password endpoint requires authentication', function () {
    $this->postJson('/api/v1/ipmart/change-proxy-password', [
        'ipmart_id' => 'ipmart-123',
        'proxyPwd' => 'new-password-123',
    ])->assertUnauthorized();
});

test('change proxy password updates ipmart record for authenticated user', function () {
    $user = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-123',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'old-password',
        'login_name' => 'proxy-login',
        'passwd' => 'ipmart-pass',
    ]);

    Sanctum::actingAs($user);

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('changeProxyPassword')
        ->once()
        ->with('ipmart-123', 'new-password-123')
        ->andReturn(['status' => 'ok']);

    $this->postJson('/api/v1/ipmart/change-proxy-password', [
        'ipmart_id' => 'ipmart-123',
        'proxyPwd' => 'new-password-123',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Proxy password changed successfully.')
        ->assertJsonPath('data.ipmart_id', 'ipmart-123')
        ->assertJsonPath('data.proxyPwd', 'new-password-123');

    $this->assertDatabaseHas('ipmarts', [
        'user_id' => (string) $user->id,
        'ipmart_id' => 'ipmart-123',
        'proxyPwd' => 'new-password-123',
    ]);
});

test('change proxy password rejects ipmart account not owned by user', function () {
    $authenticatedUser = User::factory()->create();
    $otherUser = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $otherUser->id,
    ], [
        'ipmart_id' => 'ipmart-other',
        'proxyName' => 'other-proxy',
        'proxyPwd' => 'other-old-password',
        'login_name' => 'other-login',
        'passwd' => 'other-ipmart-pass',
    ]);

    Sanctum::actingAs($authenticatedUser);

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('changeProxyPassword')
        ->never();

    $this->postJson('/api/v1/ipmart/change-proxy-password', [
        'ipmart_id' => 'ipmart-other',
        'proxyPwd' => 'new-password-456',
    ])
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'IPmart account not found for this user.');

    $this->assertDatabaseHas('ipmarts', [
        'user_id' => (string) $otherUser->id,
        'ipmart_id' => 'ipmart-other',
        'proxyPwd' => 'other-old-password',
    ]);
});
