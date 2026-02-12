<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireSubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire active subscriptions that have passed their end date.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();

        $query = Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $now);

        $expiredCount = (clone $query)->count();

        $query->update([
            'status' => Subscription::STATUS_CANCELED,
            'canceled_at' => $now,
        ]);

        $this->info("Expired {$expiredCount} subscription(s).");

        return self::SUCCESS;
    }
}
