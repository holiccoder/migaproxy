<?php

use App\Models\Ipmart;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('proxy api link endpoint requires authentication', function () {
    $this->getJson('/api/v1/ipmart/proxy-api-link')
        ->assertUnauthorized();
});

test('proxy api link endpoint validates required fields', function () {
    $user = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-123',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login-name',
        'passwd' => 'proxy-login-password',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/ipmart/proxy-api-link')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cntryCode', 'time', 'num', 'format']);
});

test('proxy api link endpoint validates field types', function () {
    $user = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-123',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login-name',
        'passwd' => 'proxy-login-password',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/ipmart/proxy-api-link?'.http_build_query([
        'cntryCode' => 'TOOLONG',
        'time' => 'not-a-number',
        'num' => -1,
        'format' => 3,
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cntryCode', 'time', 'num', 'format']);
});

test('proxy api link endpoint returns data on success with proxy host replaced', function () {
    $user = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-123',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login-name',
        'passwd' => 'proxy-login-password',
    ]);

    Sanctum::actingAs($user);

    $dataRequest = Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest');
    $dataRequest->shouldReceive('generateAPILink')
        ->once()
        ->with('CA', 'ipmart-123', 'US', 5, 1, 1, null, null)
        ->andReturn([
            'link' => 'http://proxy.ipmart.io:8080',
            'ips' => ['proxy.ipmart.io:8080:user:pass', '1.2.3.4:80:user:pass'],
        ]);

    $this->getJson('/api/v1/ipmart/proxy-api-link?'.http_build_query([
        'cntryCode' => 'US',
        'time' => 5,
        'num' => 1,
        'format' => 1,
    ]))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.link', 'http://proxy.migaproxy.com:8080')
        ->assertJsonPath('data.ips.0', 'proxy.migaproxy.com:8080')
        ->assertJsonPath('data.ips.1', '1.2.3.4:80');
});

test('proxy api link endpoint returns 500 when no valid ip endpoints are returned', function () {
    $user = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-123',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login-name',
        'passwd' => 'proxy-login-password',
    ]);

    Sanctum::actingAs($user);

    $dataRequest = Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest');
    $dataRequest->shouldReceive('generateAPILink')
        ->once()
        ->andReturn([
            'link' => 'http://proxy.ipmart.io:8080',
            'ips' => ['not-an-endpoint'],
        ]);

    $this->getJson('/api/v1/ipmart/proxy-api-link?'.http_build_query([
        'cntryCode' => 'US',
        'time' => 5,
        'num' => 1,
        'format' => 1,
    ]))
        ->assertStatus(500)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'IPmart returned no valid proxy endpoints in ip:port format.');
});

test('proxy api link endpoint returns 500 when ipmart fails', function () {
    $user = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-123',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login-name',
        'passwd' => 'proxy-login-password',
    ]);

    Sanctum::actingAs($user);

    $dataRequest = Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest');
    $dataRequest->shouldReceive('generateAPILink')
        ->once()
        ->andReturn(null);

    $this->getJson('/api/v1/ipmart/proxy-api-link?'.http_build_query([
        'cntryCode' => 'US',
        'time' => 5,
        'num' => 1,
        'format' => 1,
    ]))
        ->assertStatus(500)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Failed to generate proxy API link from IPmart.');
});

test('proxy api link endpoint returns 404 when ipmart account is missing', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $dataRequest = Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest');
    $dataRequest->shouldReceive('generateAPILink')
        ->never();

    $this->getJson('/api/v1/ipmart/proxy-api-link?'.http_build_query([
        'cntryCode' => 'US',
        'time' => 5,
        'num' => 1,
        'format' => 1,
    ]))
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'IPmart account not found for this user.');
});
