<?php

use App\Models\Ipmart;
use App\Models\TrafficHistory;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('traffic history by user id endpoint requires authentication', function () {
    $this->getJson('/api/v1/ipmart/traffic-history/1')
        ->assertUnauthorized();
});

test('traffic history by user id endpoint returns sample records', function () {
    $user = User::factory()->create();
    $timestamp = now();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => '68e77560d247fca264c188ab',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login',
        'passwd' => 'ipmart-password',
    ]);

    TrafficHistory::query()->insert([
        [
            'success_count' => 18893,
            'length' => '427.02029',
            'request_date' => '2024-09-04 08:00:00',
            'total_requests' => 23207,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'success_count' => 14437,
            'length' => '312.237332',
            'request_date' => '2024-09-03 08:00:00',
            'total_requests' => 17645,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'success_count' => 21180,
            'length' => '491.394114',
            'request_date' => '2024-09-02 08:00:00',
            'total_requests' => 25271,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'success_count' => 11426,
            'length' => '246.37840100000003',
            'request_date' => '2024-09-01 08:00:00',
            'total_requests' => 13424,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'success_count' => 6435,
            'length' => '193.216984',
            'request_date' => '2024-08-31 08:00:00',
            'total_requests' => 7732,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'success_count' => 12872,
            'length' => '259.300473',
            'request_date' => '2024-08-30 08:00:00',
            'total_requests' => 14712,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'success_count' => 19264,
            'length' => '496.028893',
            'request_date' => '2024-08-29 08:00:00',
            'total_requests' => 22034,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'success_count' => 26768,
            'length' => '476.828504',
            'request_date' => '2024-08-28 08:00:00',
            'total_requests' => 30845,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'success_count' => 19681,
            'length' => '405.774784',
            'request_date' => '2024-08-27 08:00:00',
            'total_requests' => 23618,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'success_count' => 23675,
            'length' => '396.977876',
            'request_date' => '2024-08-26 08:00:00',
            'total_requests' => 28272,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
        [
            'success_count' => 12494,
            'length' => '288.491472',
            'request_date' => '2024-08-25 08:00:00',
            'total_requests' => 14088,
            'ipmart_id' => '68e77560d247fca264c188ab',
            'provider' => 'ipmart',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ],
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/ipmart/traffic-history/'.$user->id)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total', 11)
        ->assertJsonPath('data.data.0.success_count', 18893)
        ->assertJsonPath('data.data.0.total_requests', 23207)
        ->assertJsonPath('data.data.0.ipmart_id', '68e77560d247fca264c188ab');
});

test('traffic history by user id endpoint returns authenticated user records', function () {
    $user = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-user-1',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login',
        'passwd' => 'ipmart-password',
    ]);

    TrafficHistory::query()->create([
        'success_count' => 5,
        'length' => 2.5,
        'request_date' => '2026-03-14 12:00:00',
        'total_requests' => 8,
        'ipmart_id' => 'ipmart-user-1',
        'provider' => 'ipmart',
    ]);

    $latestTrafficHistory = TrafficHistory::query()->create([
        'success_count' => 9,
        'length' => 4.75,
        'request_date' => '2026-03-15 12:00:00',
        'total_requests' => 10,
        'ipmart_id' => 'ipmart-user-1',
        'provider' => 'ipmart',
    ]);

    TrafficHistory::query()->create([
        'success_count' => 99,
        'length' => 9.99,
        'request_date' => '2026-03-15 12:00:00',
        'total_requests' => 100,
        'ipmart_id' => 'ipmart-user-other',
        'provider' => 'ipmart',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/ipmart/traffic-history/'.$user->id)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total', 2)
        ->assertJsonPath('data.data.0.id', $latestTrafficHistory->id)
        ->assertJsonPath('data.data.0.ipmart_id', 'ipmart-user-1')
        ->assertJsonPath('data.data.0.total_requests', 10);
});

test('traffic history by user id endpoint forbids access to another user id', function () {
    $targetUser = User::factory()->create();
    $authenticatedUser = User::factory()->create();

    Sanctum::actingAs($authenticatedUser);

    $this->getJson('/api/v1/ipmart/traffic-history/'.$targetUser->id)
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'You are not authorized to access this traffic history.');
});

test('traffic history by user id endpoint returns not found when ipmart account is missing', function () {
    $user = User::factory()->create();
    Ipmart::query()->where('user_id', (string) $user->id)->delete();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/ipmart/traffic-history/'.$user->id)
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'IPmart account not found for this user.');
});
