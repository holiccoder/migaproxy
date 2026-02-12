<?php

use App\Models\Plan;

test('plans index returns only active plans', function () {
    $activePlan = Plan::factory()->create(['is_active' => true]);
    $inactivePlan = Plan::factory()->inactive()->create();

    $response = $this->getJson('/api/v1/plans');

    $response->assertOk();

    $planIds = collect($response->json('data'))->pluck('id')->all();

    expect($planIds)
        ->toContain($activePlan->id)
        ->not->toContain($inactivePlan->id);
});

test('plan creation endpoint is not available to api users', function () {
    $this->postJson('/api/v1/plans', [
        'name' => 'Monthly Pro',
        'slug' => 'monthly-pro',
        'amount' => 9900,
        'currency' => 'USD',
        'interval_unit' => 'month',
        'interval_count' => 1,
    ])->assertMethodNotAllowed();
});
