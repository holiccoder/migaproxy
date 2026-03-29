<?php

use App\Models\Ipmart;
use App\Models\User;

test('command tests ipmart proxy-api-link endpoint successfully', function () {
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

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('generateAPILink')
        ->once()
        ->with('CA', 'ipmart-123', 'US', 5, 1, 1, 'California', 'Pittsburg')
        ->andReturn([
            'link' => 'http://45.10.20.30:8080',
            'ips' => ['45.10.20.30:8080:user:pass'],
        ]);

    $this->artisan('ipmart:test-proxy-api-link-endpoint '.$user->id.' --cntry-code=US --time=5 --num=1 --format=1 --state-name=California --city-name=Pittsburg')
        ->expectsOutput('IPmart proxy-api-link endpoint responded successfully.')
        ->assertSuccessful();
});
