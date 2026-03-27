<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TruncateUsersAndIpmartsCommand extends Command
{
    /**
     * @var list<string>
     */
    private const TABLES_TO_TRUNCATE = [
        'affiliate_clicks',
        'affiliate_conversions',
        'ticket_messages',
        'subscriptions',
        'orders',
        'post_comments',
        'balance_histories',
        'tickets',
        'affiliates',
        'traffic_histories',
        'ipmarts',
        'users',
    ];

    /**
     * @var array<string, string>
     */
    private const USER_SCOPED_DELETE_TABLES = [
        'personal_access_tokens' => 'tokenable_type',
        'notifications' => 'notifiable_type',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:truncate {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate users, ipmarts, and user-related tables.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will permanently delete users, ipmarts, and related data. Continue?', false)) {
            $this->warn('Command cancelled.');

            return self::SUCCESS;
        }

        $truncatedCounts = [];
        $deletedCounts = [];

        Schema::disableForeignKeyConstraints();

        try {
            foreach (self::TABLES_TO_TRUNCATE as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $truncatedCounts[$table] = (int) DB::table($table)->count();
                $this->clearTable($table);
            }

            foreach (self::USER_SCOPED_DELETE_TABLES as $table => $morphTypeColumn) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $deletedCounts[$table] = DB::table($table)
                    ->where($morphTypeColumn, User::class)
                    ->delete();
            }
        } catch (Throwable $throwable) {
            $this->error('Failed to truncate user-related data: '.$throwable->getMessage());

            return self::FAILURE;
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info('Users, ipmarts, and related tables were cleared successfully.');

        foreach ($truncatedCounts as $table => $count) {
            $this->line("- {$table}: {$count} row(s) truncated.");
        }

        foreach ($deletedCounts as $table => $count) {
            $this->line("- {$table}: {$count} user row(s) deleted.");
        }

        return self::SUCCESS;
    }

    private function clearTable(string $table): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::table($table)->delete();

            return;
        }

        DB::table($table)->truncate();
    }
}
