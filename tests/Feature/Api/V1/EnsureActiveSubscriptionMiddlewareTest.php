<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function registerSubscribedTestRoute(): void
{
    app('router')->middleware(['auth:sanctum', 'subscribed'])->get('/api/v1/test/subscribed', function () {
        return response()->json([
            'message' => 'Allowed',
        ]);
    });
}

test('user with active subscription can access subscribed route', function () {
    registerSubscribedTestRoute();

    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'order_id' => null,
        'status' => Subscription::STATUS_ACTIVE,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addMonth(),
        'canceled_at' => null,
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/test/subscribed')
        ->assertOk()
        ->assertJsonPath('message', 'Allowed');
});

test('user with expired subscription cannot access subscribed route', function () {
    registerSubscribedTestRoute();

    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'order_id' => null,
        'status' => Subscription::STATUS_ACTIVE,
        'starts_at' => now()->subMonths(2),
        'ends_at' => now()->subDay(),
        'canceled_at' => null,
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/test/subscribed')
        ->assertForbidden()
        ->assertJsonPath('message', 'An active subscription is required to access this resource.');
});

test('user without subscription cannot access subscribed route', function () {
    registerSubscribedTestRoute();

    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/test/subscribed')
        ->assertForbidden()
        ->assertJsonPath('message', 'An active subscription is required to access this resource.');
});
