<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('ipmart proxy options endpoint requires authentication', function () {
    $this->getJson('/api/v1/ipmart/proxy-options')
        ->assertUnauthorized();
});

test('ipmart proxy options endpoint returns countries protocols patterns rules and test link', function () {
    Sanctum::actingAs(User::factory()->create());

    $dataRequest = Mockery::mock('overload:App\\Services\\Api\\IPmart\\DataRequest');
    $dataRequest->shouldReceive('getCountries')
        ->once()
        ->andReturn([
            ['code' => 'US', 'name' => 'United States'],
            ['code' => 'GB', 'name' => 'United Kingdom'],
        ]);
    $dataRequest->shouldReceive('chooseProtocol')
        ->once()
        ->andReturn([
            ['id' => 0, 'name' => 'HTTP'],
            ['id' => 2, 'name' => 'SOCKS5'],
        ]);
    $dataRequest->shouldReceive('proxyPattern')
        ->once()
        ->andReturn([
            ['id' => 1, 'name' => 'Sticky'],
            ['id' => 2, 'name' => 'Rotating'],
        ]);
    $dataRequest->shouldReceive('getProxyRules')
        ->once()
        ->andReturn([
            ['id' => 0, 'name' => 'Rotating'],
            ['id' => 1, 'name' => 'Sticky 5-30 minutes'],
        ]);

    $this->getJson('/api/v1/ipmart/proxy-options')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.countries.0.code', 'US')
        ->assertJsonPath('data.protocols.0.name', 'HTTP')
        ->assertJsonPath('data.patterns.1.name', 'Rotating')
        ->assertJsonPath('data.rules.1.name', 'Sticky 5-30 minutes')
        ->assertJsonPath('data.test_link', '');
});
