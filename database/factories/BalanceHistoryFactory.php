<?php

namespace Database\Factories;

use App\Models\BalanceHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BalanceHistory>
 */
class BalanceHistoryFactory extends Factory
{
    protected $model = BalanceHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $beforeBalance = fake()->numberBetween(0, 50000);
        $direction = fake()->randomElement([1, -1]);
        $amount = fake()->numberBetween(100, 10000) * $direction;
        $afterBalance = max(0, $beforeBalance + $amount);

        return [
            'user_id' => User::factory(),
            'type' => $amount >= 0 ? BalanceHistory::TYPE_CREDIT : BalanceHistory::TYPE_DEBIT,
            'amount' => $amount,
            'before_balance' => $beforeBalance,
            'after_balance' => $afterBalance,
            'reference' => strtoupper(fake()->bothify('BAL-#####')),
            'description' => fake()->sentence(),
        ];
    }
}
