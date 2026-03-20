<?php

use App\Models\BalanceHistory;
use App\Models\Coupon;
use App\Models\Ipmart;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('authenticated user can purchase plan with wallet', function () {
    $user = User::factory()->create([
        'balance' => 30000,
    ]);
    $plan = Plan::factory()->create([
        'price' => 19900,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'payment_method' => 'wallet',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Payment successful. Plan ordered and subscription activated.')
        ->assertJsonPath('data.order.status', Order::STATUS_PAID)
        ->assertJsonPath('data.order.subtotal', 19900)
        ->assertJsonPath('data.order.discount_total', 0)
        ->assertJsonPath('data.order.total', 19900)
        ->assertJsonPath('data.subscription.status', Subscription::STATUS_ACTIVE)
        ->assertJsonPath('data.wallet.balance', 10100)
        ->assertJsonPath('data.wallet.balance_formatted', '101.00');

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => Order::STATUS_PAID,
        'subtotal' => 19900,
        'discount_total' => 0,
        'total' => 19900,
    ]);

    $this->assertDatabaseHas('balance_histories', [
        'user_id' => $user->id,
        'type' => BalanceHistory::TYPE_DEBIT,
        'amount' => -19900,
        'before_balance' => 30000,
        'after_balance' => 10100,
    ]);

    $user->refresh();
    expect($user->balance)->toBe(10100);
});

test('checkout places ipmart order using plan traffic and user id when enable_order is enabled', function () {
    config()->set('payments.enable_order', true);

    $user = User::factory()->create([
        'balance' => 30000,
    ]);
    $plan = Plan::factory()->create([
        'price' => 19900,
        'traffic' => 20,
    ]);

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-checkout-123',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login-name',
        'passwd' => 'proxy-login-password',
        'plan_balance' => 12,
    ]);

    $ipmartOrderPayload = [
        'order_no' => 'ORDER-456',
        'plan_balance' => 88,
    ];

    Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldReceive('payForCustomerUsingBalance')
        ->once()
        ->with('ipmart-checkout-123', 20)
        ->andReturn($ipmartOrderPayload);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'payment_method' => 'wallet',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.order.status', Order::STATUS_PAID);

    $order = Order::query()->findOrFail((int) $response->json('data.order.id'));

    expect($order->ipmart_order)->toBe($ipmartOrderPayload);

    $this->assertDatabaseHas('ipmarts', [
        'user_id' => (string) $user->id,
        'ipmart_id' => 'ipmart-checkout-123',
        'plan_balance' => 88,
    ]);
});

test('checkout applies percentage coupon discount with wallet payment', function () {
    $user = User::factory()->create([
        'balance' => 30000,
    ]);
    $plan = Plan::factory()->create([
        'price' => 20000,
    ]);
    $coupon = Coupon::factory()->create([
        'code' => 'SAVE10',
        'type' => Coupon::TYPE_PERCENTAGE,
        'value' => 10,
        'is_active' => true,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'payment_method' => 'wallet',
        'coupon_code' => $coupon->code,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.order.status', Order::STATUS_PAID)
        ->assertJsonPath('data.order.coupon_code', 'SAVE10')
        ->assertJsonPath('data.order.subtotal', 20000)
        ->assertJsonPath('data.order.discount_total', 2000)
        ->assertJsonPath('data.order.total', 18000)
        ->assertJsonPath('data.wallet.balance', 12000);

    $coupon->refresh();
    expect($coupon->used_count)->toBe(1);
});

test('checkout applies fixed coupon and never goes below zero with wallet payment', function () {
    $user = User::factory()->create([
        'balance' => 1000,
    ]);
    $plan = Plan::factory()->create([
        'price' => 2000,
    ]);
    $coupon = Coupon::factory()->fixedAmount()->create([
        'code' => 'TAKE5000',
        'value' => 5000,
        'is_active' => true,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'payment_method' => 'wallet',
        'coupon_code' => $coupon->code,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.order.status', Order::STATUS_PAID)
        ->assertJsonPath('data.order.subtotal', 2000)
        ->assertJsonPath('data.order.discount_total', 2000)
        ->assertJsonPath('data.order.total', 0)
        ->assertJsonPath('data.wallet.balance', 1000);

    $this->assertDatabaseHas('balance_histories', [
        'user_id' => $user->id,
        'type' => BalanceHistory::TYPE_DEBIT,
        'amount' => 0,
        'before_balance' => 1000,
        'after_balance' => 1000,
    ]);
});

test('checkout rejects invalid coupon code', function () {
    $user = User::factory()->create([
        'balance' => 20000,
    ]);
    $plan = Plan::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'payment_method' => 'wallet',
        'coupon_code' => 'UNKNOWN',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Coupon code is invalid.');
});

test('checkout rejects unknown plans', function () {
    $user = User::factory()->create([
        'balance' => 20000,
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/checkout', [
        'plan_id' => 999999,
        'payment_method' => 'wallet',
    ])->assertUnprocessable();
});

test('checkout rejects non wallet payment methods for now', function () {
    $user = User::factory()->create([
        'balance' => 20000,
    ]);
    $plan = Plan::factory()->create([
        'price' => 15000,
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'payment_method' => 'credit_card',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Only wallet payment is currently supported.');
});

test('checkout rejects when wallet balance is insufficient', function () {
    $user = User::factory()->create([
        'balance' => 1000,
    ]);
    $plan = Plan::factory()->create([
        'price' => 15000,
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'payment_method' => 'wallet',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Insufficient wallet balance.');

    $this->assertDatabaseMissing('orders', [
        'user_id' => $user->id,
        'plan_id' => $plan->id,
    ]);
});

test('guest cannot create checkout order', function () {
    $plan = Plan::factory()->create();

    $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'payment_method' => 'wallet',
    ])->assertUnauthorized();
});
