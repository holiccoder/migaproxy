<?php

use App\Models\Ipmart;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

test('webhook stores ipmart order payload for paid order', function () {
    config()->set('payments.enable_order', true);

    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'traffic' => 1,
    ]);

    Ipmart::query()->updateOrCreate([
        'user_id' => (string) $user->id,
    ], [
        'ipmart_id' => 'ipmart-paid-1',
        'proxyName' => 'proxy-name',
        'proxyPwd' => 'proxy-password',
        'login_name' => 'proxy-login',
        'passwd' => 'ipmart-password',
    ]);

    $order = Order::factory()->create([
        'public_id' => Str::ulid()->toBase32(),
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => Order::STATUS_PENDING,
    ]);

    $ipmartOrderPayload = [
        'status' => 200,
        'errorMsg' => null,
        'data' => [
            'no' => '20240905153756202',
            'paid' => true,
            'id' => '66d95fd43e28885e230f3137',
            'order_time' => '2024-09-05 15:37:56',
            'capacity' => 1,
            'status' => '1',
            'pay_time' => '2024-09-05 15:37:56',
        ],
    ];

    $dataRequest = Mockery::mock('alias:App\\Services\\Api\\IPmart\\DataRequest')
        ->shouldIgnoreMissing();

    $dataRequest->shouldReceive('payForCustomerUsingBalance')
        ->once()
        ->with('ipmart-paid-1', 1)
        ->andReturn($ipmartOrderPayload);

    $response = $this->postJson('/api/v1/payments/webhooks/fake', [
        'event' => 'checkout.completed',
        'order_public_id' => $order->public_id,
        'provider_reference' => 'fake_checkout_123',
        'subscription_reference' => 'sub_123',
    ]);

    $response->assertAccepted();

    $order->refresh();

    expect($order->ipmart_order)->toBe($ipmartOrderPayload);
});

test('webhook marks order paid and creates subscription', function () {
    $now = now()->startOfSecond();
    Carbon::setTestNow($now);

    $user = User::factory()->create();
    Ipmart::query()->where('user_id', (string) $user->id)->delete();

    $plan = Plan::factory()->create([
        'days' => 90,
    ]);
    $order = Order::factory()->create([
        'public_id' => Str::ulid()->toBase32(),
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => Order::STATUS_PENDING,
    ]);

    $response = $this->postJson('/api/v1/payments/webhooks/fake', [
        'event' => 'checkout.completed',
        'order_public_id' => $order->public_id,
        'provider_reference' => 'fake_checkout_123',
        'subscription_reference' => 'sub_123',
    ]);

    $response->assertAccepted();

    $order->refresh();

    expect($order->status)->toBe(Order::STATUS_PAID);
    expect($order->paid_at?->equalTo($now))->toBeTrue();

    $subscription = Subscription::query()->where('order_id', $order->id)->first();

    expect($subscription)->not->toBeNull();
    expect($subscription?->status)->toBe(Subscription::STATUS_ACTIVE);
    expect($subscription?->starts_at?->equalTo($now))->toBeTrue();
    expect($subscription?->ends_at?->equalTo($now->copy()->addDays(90)))->toBeTrue();

    Carbon::setTestNow();
});

test('webhook marks order failed on payment failure', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $order = Order::factory()->create([
        'public_id' => Str::ulid()->toBase32(),
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => Order::STATUS_PENDING,
    ]);

    $response = $this->postJson('/api/v1/payments/webhooks/fake', [
        'event' => 'invoice.payment_failed',
        'order_public_id' => $order->public_id,
    ]);

    $response->assertAccepted();

    $order->refresh();

    expect($order->status)->toBe(Order::STATUS_FAILED);
    expect($order->failed_at)->not->toBeNull();
});
