<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('proxy api link endpoint requires authentication', function () {
    $this->getJson('/api/v1/ipmart/proxy-api-link')
        ->assertUnauthorized();
});

test('proxy api link endpoint validates required fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/ipmart/proxy-api-link')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['subUserId', 'cntryCode', 'time', 'num', 'format']);
});

test('proxy api link endpoint validates field types', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/ipmart/proxy-api-link?'.http_build_query([
        'subUserId' => '68e77560d247fca264c188ab',
        'cntryCode' => 'TOOLONG',
        'time' => 'not-a-number',
        'num' => -1,
        'format' => 'txt',
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cntryCode', 'time', 'num']);
});

test('proxy api link endpoint returns data on success', function () {
    Sanctum::actingAs(User::factory()->create());

    $dataRequest = Mockery::mock('overload:App\\Services\\Api\\IPmart\\DataRequest');
    $dataRequest->shouldReceive('generateAPILink')
        ->once()
        ->andReturn([
            'link' => 'http://proxy.example.com:8080',
            'ips' => ['1.2.3.4:8080'],
        ]);

    $this->getJson('/api/v1/ipmart/proxy-api-link?'.http_build_query([
        'subUserId' => '68e77560d247fca264c188ab',
        'cntryCode' => 'US',
        'time' => 5,
        'num' => 1,
        'format' => 'txt',
    ]))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['success', 'data']);
});

test('proxy api link endpoint returns 500 when ipmart fails', function () {
    Sanctum::actingAs(User::factory()->create());

    $dataRequest = Mockery::mock('overload:App\\Services\\Api\\IPmart\\DataRequest');
    $dataRequest->shouldReceive('generateAPILink')
        ->once()
        ->andReturn(null);

    $this->getJson('/api/v1/ipmart/proxy-api-link?'.http_build_query([
        'subUserId' => '68e77560d247fca264c188ab',
        'cntryCode' => 'US',
        'time' => 5,
        'num' => 1,
        'format' => 'txt',
    ]))
        ->assertStatus(500)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Failed to generate proxy API link from IPmart');
});
