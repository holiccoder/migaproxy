<?php

namespace App\Actions\Notifications;

use App\Models\User;
use App\Notifications\AdminPrivateMessageNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkPrivateMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?int $adminId,
        public ?string $adminName,
        public string $subject,
        public string $message,
        public bool $sendToEmail = false,
    ) {}

    public function handle(): void
    {
        User::query()
            ->select(['id', 'name', 'email'])
            ->chunkById(200, function ($users): void {
                foreach ($users as $user) {
                    $user->notify(new AdminPrivateMessageNotification(
                        subject: $this->subject,
                        message: $this->message,
                        adminId: $this->adminId,
                        adminName: $this->adminName,
                        sendToEmail: $this->sendToEmail,
                    ));
                }
            });
    }
}
