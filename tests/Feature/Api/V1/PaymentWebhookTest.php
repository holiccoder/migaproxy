<?php

use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

test('webhook marks order paid and creates subscription', function () {
    $now = now()->startOfSecond();
    Carbon::setTestNow($now);

    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'interval_unit' => 'month',
        'interval_count' => 3,
    ]);
    $order = Order::factory()->create([
        'public_id' => Str::ulid()->toBase32(),
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'provider' => 'fake',
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
    expect($subscription?->ends_at?->equalTo($now->copy()->addMonthsNoOverflow(3)))->toBeTrue();

    Carbon::setTestNow();
});

test('webhook marks order failed on payment failure', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $order = Order::factory()->create([
        'public_id' => Str::ulid()->toBase32(),
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'provider' => 'fake',
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
