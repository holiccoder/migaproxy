<?php

use App\Models\Ipmart;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('pay for customer using balance endpoint requires authentication', function () {
    $this->postJson('/api/v1/ipmart/pay-for-customer-using-balance', [
        'amount' => 1,
    ])->assertUnauthorized();
});

test('pay for customer using balance endpoint validates required amount', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/ipmart/pay-for-customer-using-balance', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount']);
});

test('pay for customer using balance endpoint returns not found when ipmart account is missing', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('payForCustomerUsingBalance')
        ->never();

    $this->postJson('/api/v1/ipmart/pay-for-customer-using-balance', [
        'amount' => 5,
    ])
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'IPmart account not found for this user.');
});

test('pay for customer using balance endpoint places order and updates plan balance', function () {
    $user = User::factory()->create();

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-123',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login-name',
        'passwd' => 'proxy-login-password',
        'plan_balance' => 10,
    ]);

    Sanctum::actingAs($user);

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('payForCustomerUsingBalance')
        ->once()
        ->with('ipmart-123', 15)
        ->andReturn([
            'order_no' => 'ORDER-123',
            'plan_balance' => 120,
        ]);

    $this->postJson('/api/v1/ipmart/pay-for-customer-using-balance', [
        'amount' => 15,
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Customer order placed successfully.')
        ->assertJsonPath('data.user_id', $user->id)
        ->assertJsonPath('data.ipmart_id', 'ipmart-123')
        ->assertJsonPath('data.amount', 15)
        ->assertJsonPath('data.order.order_no', 'ORDER-123')
        ->assertJsonPath('data.order.plan_balance', 120);

    $this->assertDatabaseHas('ipmarts', [
        'user_id' => (string) $user->id,
        'ipmart_id' => 'ipmart-123',
        'plan_balance' => 120,
    ]);
});
