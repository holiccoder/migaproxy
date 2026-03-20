<?php

namespace App\Actions\IPmart;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class CheckUserIpmartAvailableTrafficJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $userId) {}

    public function handle(): void
    {
        $user = User::query()
            ->withExists('ipmart')
            ->find($this->userId, ['id', 'email_verified_at']);

        if (! $user instanceof User) {
            return;
        }

        if (! $user->hasVerifiedEmail() || ! (bool) $user->ipmart_exists) {
            return;
        }

        $exitCode = Artisan::call('ipmart:check-proxy-info', [
            'user_id' => $user->id,
        ]);

        if ($exitCode !== SymfonyCommand::SUCCESS) {
            Log::warning('Queued IPmart proxy info check failed.', [
                'user_id' => $user->id,
                'exit_code' => $exitCode,
                'output' => trim(Artisan::output()),
            ]);
        }
    }
}
