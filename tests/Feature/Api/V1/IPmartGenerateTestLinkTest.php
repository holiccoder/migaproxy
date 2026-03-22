<?php

use App\Models\Ipmart;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('generate test link endpoint requires authentication', function () {
    $this->getJson('/api/v1/ipmart/generate-test-link')
        ->assertUnauthorized();
});

test('generate test link endpoint replaces ipmart proxy host in response payload', function () {
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

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('generateProxyLinks')
        ->once()
        ->with('ipmart-123', 0, 1, 1, 2, 'US', null, null)
        ->andReturn([
            'http_link' => 'http://proxy.ipmart.io:10000',
            'socks5_link' => 'socks5://proxy.ipmart.io:10001',
            'ips' => [
                'proxy.ipmart.io:10000:user:pass',
                '1.1.1.1:80:user:pass',
            ],
            'metadata' => [
                'raw_link' => 'https://proxy.ipmart.io/path?country=US',
            ],
        ]);

    $response = $this->getJson('/api/v1/ipmart/generate-test-link?'.http_build_query([
        'protocol' => 0,
        'pattern' => 1,
        'rule' => 1,
        'count' => 2,
        'country' => 'US',
    ]));

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.http_link', 'http://proxy.migaproxy.com:10000')
        ->assertJsonPath('data.socks5_link', 'socks5://proxy.migaproxy.com:10001')
        ->assertJsonPath('data.ips.0', 'proxy.migaproxy.com:10000:user:pass')
        ->assertJsonPath('data.ips.1', '1.1.1.1:80:user:pass')
        ->assertJsonPath('data.metadata.raw_link', 'https://proxy.migaproxy.com/path?country=US');
});
