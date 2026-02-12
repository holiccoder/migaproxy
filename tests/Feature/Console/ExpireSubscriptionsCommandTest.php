<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

test('command expires only active subscriptions that reached end date', function () {
    $now = now()->startOfSecond();
    Carbon::setTestNow($now);

    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $expiredActive = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'order_id' => null,
        'status' => Subscription::STATUS_ACTIVE,
        'starts_at' => $now->copy()->subMonth(),
        'ends_at' => $now->copy()->subMinute(),
        'canceled_at' => null,
    ]);

    $stillActive = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'order_id' => null,
        'status' => Subscription::STATUS_ACTIVE,
        'starts_at' => $now->copy()->subDay(),
        'ends_at' => $now->copy()->addDay(),
        'canceled_at' => null,
    ]);

    $pastDue = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'order_id' => null,
        'status' => Subscription::STATUS_PAST_DUE,
        'starts_at' => $now->copy()->subMonth(),
        'ends_at' => $now->copy()->subDay(),
        'canceled_at' => null,
    ]);

    $this->artisan('subscriptions:expire')
        ->expectsOutput('Expired 1 subscription(s).')
        ->assertSuccessful();

    $expiredActive->refresh();
    $stillActive->refresh();
    $pastDue->refresh();

    expect($expiredActive->status)->toBe(Subscription::STATUS_CANCELED);
    expect($expiredActive->canceled_at?->equalTo($now))->toBeTrue();
    expect($stillActive->status)->toBe(Subscription::STATUS_ACTIVE);
    expect($stillActive->canceled_at)->toBeNull();
    expect($pastDue->status)->toBe(Subscription::STATUS_PAST_DUE);
    expect($pastDue->canceled_at)->toBeNull();

    Carbon::setTestNow();
});
