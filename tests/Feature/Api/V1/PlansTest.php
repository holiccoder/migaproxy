<?php

use App\Models\Plan;

test('plans index returns plans sorted by price', function () {
    $higherPricePlan = Plan::factory()->create(['price' => 19900]);
    $lowerPricePlan = Plan::factory()->create(['price' => 9900]);

    $response = $this->getJson('/api/v1/plans');

    $response->assertOk();

    $planIds = collect($response->json('data'))->pluck('id')->all();

    expect($planIds)
        ->toBe([$lowerPricePlan->id, $higherPricePlan->id]);
});

test('plan creation endpoint is not available to api users', function () {
    $this->postJson('/api/v1/plans', [
        'name' => 'Monthly Pro',
        'traffic' => 1000,
        'description' => 'Starter plan',
        'price' => 9900,
        'days' => 30,
    ])->assertMethodNotAllowed();
});
