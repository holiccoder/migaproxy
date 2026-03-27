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
    $buyer = User::factory()->create([
        'balance' => 20000,
    ]);
    $plan = Plan::factory()->create([
        'price' => 10000,
    ]);

    Sanctum::actingAs($buyer);

    $response = $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'payment_method' => 'wallet',
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

test('wallet checkout creates affiliate conversion immediately', function () {
    $affiliateOwner = User::factory()->create();
    $affiliate = Affiliate::factory()->create([
        'user_id' => $affiliateOwner->id,
        'code' => 'AFFPAY',
        'commission_type' => Affiliate::COMMISSION_TYPE_PERCENTAGE,
        'commission_value' => 10,
        'is_active' => true,
    ]);

    $buyer = User::factory()->create([
        'balance' => 30000,
    ]);
    $plan = Plan::factory()->create([
        'price' => 15000,
    ]);

    Sanctum::actingAs($buyer);

    $checkout = $this->postJson('/api/v1/checkout', [
        'plan_id' => $plan->id,
        'payment_method' => 'wallet',
        'affiliate_code' => 'AFFPAY',
    ])->assertCreated();

    $orderId = (int) $checkout->json('data.order.id');

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

test('affiliate dashboard returns referral and qr code image urls', function () {
    config()->set('app.frontend_url', 'https://frontend.example.com');

    $user = User::factory()->create();
    $affiliate = Affiliate::factory()->create([
        'user_id' => $user->id,
        'code' => 'AFFQR01',
        'is_active' => true,
    ]);

    Sanctum::actingAs($user);

    $referralUrl = 'https://frontend.example.com/?affiliate_code=AFFQR01';
    $qrCodeImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?'.http_build_query([
        'size' => '300x300',
        'format' => 'png',
        'data' => $referralUrl,
    ], '', '&', PHP_QUERY_RFC3986);

    $this->getJson('/api/v1/affiliate/dashboard')
        ->assertOk()
        ->assertJsonPath('data.affiliate.id', $affiliate->id)
        ->assertJsonPath('data.affiliate.code', 'AFFQR01')
        ->assertJsonPath('data.affiliate.referral_url', $referralUrl)
        ->assertJsonPath('data.affiliate.qr_code_image_url', $qrCodeImageUrl);
});
