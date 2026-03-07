<?php

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('authenticated user can create checkout order', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'price' => 19900,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'provider' => 'fake',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Checkout session created successfully.')
        ->assertJsonPath('data.order.status', Order::STATUS_PENDING)
        ->assertJsonPath('data.order.subtotal', 19900)
        ->assertJsonPath('data.order.discount_total', 0)
        ->assertJsonPath('data.order.total', 19900);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'provider' => 'fake',
        'status' => Order::STATUS_PENDING,
        'subtotal' => 19900,
        'discount_total' => 0,
        'total' => 19900,
    ]);
});

test('checkout applies percentage coupon discount', function () {
    $user = User::factory()->create();
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
        'provider' => 'fake',
        'coupon_code' => $coupon->code,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.order.coupon_code', 'SAVE10')
        ->assertJsonPath('data.order.subtotal', 20000)
        ->assertJsonPath('data.order.discount_total', 2000)
        ->assertJsonPath('data.order.total', 18000);
});

test('checkout applies fixed coupon and never goes below zero', function () {
    $user = User::factory()->create();
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
        'provider' => 'fake',
        'coupon_code' => $coupon->code,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.order.subtotal', 2000)
        ->assertJsonPath('data.order.discount_total', 2000)
        ->assertJsonPath('data.order.total', 0);
});

test('checkout rejects invalid coupon code', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'provider' => 'fake',
        'coupon_code' => 'UNKNOWN',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Coupon code is invalid.');
});

test('checkout rejects unknown plans', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/checkout', [
        'plan_id' => 999999,
        'provider' => 'fake',
    ])->assertUnprocessable();
});

test('guest cannot create checkout order', function () {
    $plan = Plan::factory()->create();

    $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'provider' => 'fake',
    ])->assertUnauthorized();
});
