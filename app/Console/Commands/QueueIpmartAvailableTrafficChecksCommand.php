<?php

namespace App\Console\Commands;

use App\Actions\IPmart\CheckUserIpmartAvailableTrafficJob;
use App\Models\User;
use Illuminate\Console\Command;

class QueueIpmartAvailableTrafficChecksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ipmart:queue-available-traffic-checks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue available traffic checks for all users.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dispatchedCount = 0;

        User::query()
            ->select('id')
            ->chunkById(200, function ($users) use (&$dispatchedCount): void {
                foreach ($users as $user) {
                    CheckUserIpmartAvailableTrafficJob::dispatch((int) $user->id);
                    $dispatchedCount++;
                }
            });

        $this->info("Dispatched {$dispatchedCount} IPmart available traffic check job(s).");

        return self::SUCCESS;
    }
}
