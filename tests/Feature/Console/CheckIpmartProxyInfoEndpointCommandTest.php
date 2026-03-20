<?php

use App\Models\Ipmart;
use App\Models\User;

test('command checks ipmart proxy-info endpoint and updates available traffic', function () {
    $user = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-123',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login-name',
        'passwd' => 'proxy-login-password',
        'available_traffic' => 0,
    ]);

    $dataRequest = Mockery::mock('overload:App\\Services\\Api\\IPmart\\DataRequest');
    $dataRequest->shouldReceive('getAvailableTraffic')
        ->once()
        ->with('ipmart-123')
        ->andReturn([
            'traffic_balance' => 321,
            'proxy_password' => 'new-password',
            'proxy_name' => 'new-proxy-name',
        ]);

    $this->artisan('ipmart:check-proxy-info '.$user->id)
        ->expectsOutput('IPmart proxy-info endpoint responded successfully.')
        ->assertSuccessful();

    $this->assertDatabaseHas('ipmarts', [
        'user_id' => (string) $user->id,
        'ipmart_id' => 'ipmart-123',
        'available_traffic' => 321,
    ]);
});
