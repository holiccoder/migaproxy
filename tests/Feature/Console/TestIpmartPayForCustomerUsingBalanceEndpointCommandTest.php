<?php

use App\Models\Ipmart;
use App\Models\User;

test('command tests ipmart pay-for-customer-using-balance endpoint successfully', function () {
    $user = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-123',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login-name',
        'passwd' => 'proxy-login-password',
        'plan_balance' => 12,
    ]);

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('payForCustomerUsingBalance')
        ->once()
        ->with('ipmart-123', 20)
        ->andReturn([
            'order_no' => 'ORDER-456',
            'plan_balance' => 88,
        ]);

    $this->artisan('ipmart:test-pay-for-customer-using-balance-endpoint '.$user->id.' 20')
        ->expectsOutput('IPmart pay-for-customer-using-balance endpoint responded successfully.')
        ->assertSuccessful();

    $this->assertDatabaseHas('ipmarts', [
        'user_id' => (string) $user->id,
        'ipmart_id' => 'ipmart-123',
        'plan_balance' => 88,
    ]);
});
