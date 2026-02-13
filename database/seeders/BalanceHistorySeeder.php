<?php

namespace Database\Seeders;

use App\Models\BalanceHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class BalanceHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->isEmpty()) {
            $users = User::factory()->count(3)->create();
        }

        $users->each(function (User $user): void {
            $currentBalance = 0;

            foreach (range(1, 12) as $sequence) {
                $isCredit = (bool) random_int(0, 1);
                $amount = random_int(100, 10000);
                $signedAmount = $isCredit ? $amount : -$amount;

                if (! $isCredit && ($currentBalance + $signedAmount) < 0) {
                    $signedAmount = $amount;
                }

                $beforeBalance = $currentBalance;
                $afterBalance = max(0, $beforeBalance + $signedAmount);

                BalanceHistory::query()->create([
                    'user_id' => $user->id,
                    'type' => $signedAmount >= 0 ? BalanceHistory::TYPE_CREDIT : BalanceHistory::TYPE_DEBIT,
                    'amount' => $signedAmount,
                    'before_balance' => $beforeBalance,
                    'after_balance' => $afterBalance,
                    'reference' => sprintf('BAL-%d-%d', $user->id, $sequence),
                    'description' => $signedAmount >= 0 ? 'Wallet top-up' : 'Wallet spend',
                    'created_at' => now()->subDays(13 - $sequence),
                    'updated_at' => now()->subDays(13 - $sequence),
                ]);

                $currentBalance = $afterBalance;
            }

            $user->forceFill([
                'balance' => $currentBalance,
            ])->save();
        });
    }
}
