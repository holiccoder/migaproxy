<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(),
            'status' => Ticket::STATUS_OPEN,
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'last_user_reply_at' => now(),
            'last_admin_reply_at' => null,
            'resolved_at' => null,
        ];
    }
}
