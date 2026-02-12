<?php

use App\Models\Affiliate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('affiliate track endpoint records click', function () {
    $affiliate = Affiliate::factory()->create([
        'code' => 'AFFTRACK',
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/affiliate/track?code=AFFTRACK');

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Affiliate click tracked.')
        ->assertJsonPath('data.affiliate_id', $affiliate->id);

    $this->assertDatabaseHas('affiliate_clicks', [
        'affiliate_id' => $affiliate->id,
        'code' => 'AFFTRACK',
    ]);
});

test('checkout stores affiliate attribution on order', function () {
    $affiliateOwner = User::factory()->create();
    $affiliate = Affiliate::factory()->create([
        'user_id' => $affiliateOwner->id,
        'code' => 'AFFCHECK',
        'is_active' => true,
    ]);
    $buyer = User::factory()->create();
    $plan = Plan::factory()->create([
        'amount' => 10000,
        'currency' => 'USD',
    ]);

    Sanctum::actingAs($buyer);

    $response = $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'provider' => 'fake',
        'affiliate_code' => 'AFFCHECK',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.order.affiliate_id', $affiliate->id)
        ->assertJsonPath('data.order.affiliate_code', 'AFFCHECK');

    $this->assertDatabaseHas('orders', [
        'user_id' => $buyer->id,
        'affiliate_id' => $affiliate->id,
        'affiliate_code' => 'AFFCHECK',
    ]);
});

test('successful payment webhook creates affiliate conversion', function () {
    $affiliateOwner = User::factory()->create();
    $affiliate = Affiliate::factory()->create([
        'user_id' => $affiliateOwner->id,
        'code' => 'AFFPAY',
        'commission_type' => Affiliate::COMMISSION_TYPE_PERCENTAGE,
        'commission_value' => 10,
        'is_active' => true,
    ]);

    $buyer = User::factory()->create();
    $plan = Plan::factory()->create([
        'amount' => 15000,
        'currency' => 'USD',
    ]);

    Sanctum::actingAs($buyer);

    $checkout = $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'provider' => 'fake',
        'affiliate_code' => 'AFFPAY',
    ])->assertCreated();

    $orderPublicId = (string) $checkout->json('data.order.public_id');
    $orderId = (int) $checkout->json('data.order.id');

    $this->postJson('/api/v1/payments/webhooks/fake', [
        'event' => 'checkout.completed',
        'order_public_id' => $orderPublicId,
        'provider_reference' => 'fake_ref_123',
        'subscription_reference' => 'sub_aff_123',
    ])->assertAccepted();

    $order = Order::query()->findOrFail($orderId);
    expect($order->status)->toBe(Order::STATUS_PAID);

    $this->assertDatabaseHas('affiliate_conversions', [
        'affiliate_id' => $affiliate->id,
        'order_id' => $order->id,
        'user_id' => $buyer->id,
        'amount' => 15000,
        'commission_amount' => 1500,
        'status' => 'approved',
    ]);

    $affiliate->refresh();
    expect($affiliate->total_earnings)->toBe(1500);
});
