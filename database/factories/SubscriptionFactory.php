<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = now();

        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'order_id' => Order::factory(),
            'provider' => 'fake',
            'provider_subscription_id' => 'sub_'.Str::lower(Str::random(12)),
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMonth(),
            'canceled_at' => null,
        ];
    }
}
