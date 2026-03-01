<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class CloseStaleTicketsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:close-stale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close tickets that have not received any reply in the last 3 days.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();
        $cutoff = $now->copy()->subDays(3);

        $query = Ticket::query()
            ->whereIn('status', [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS])
            ->whereDoesntHave('messages', function (Builder $query) use ($cutoff): void {
                $query->where('created_at', '>', $cutoff);
            });

        $closedCount = (clone $query)->count();

        $query->update([
            'status' => Ticket::STATUS_CLOSED,
            'resolved_at' => $now,
        ]);

        $this->info("Closed {$closedCount} stale ticket(s).");

        return self::SUCCESS;
    }
}
