<?php

namespace App\Actions\Notifications;

use App\Models\Admin;
use App\Models\User;
use App\Notifications\AdminPrivateMessageNotification;

class SendBulkPrivateMessage
{
    public function execute(?Admin $sender, string $subject, string $message): int
    {
        $sentCount = 0;

        User::query()
            ->select(['id', 'name', 'email'])
            ->chunkById(200, function ($users) use ($sender, $subject, $message, &$sentCount): void {
                foreach ($users as $user) {
                    $user->notify(new AdminPrivateMessageNotification(
                        subject: $subject,
                        message: $message,
                        adminId: $sender?->id,
                        adminName: $sender?->name
                    ));

                    $sentCount++;
                }
            });

        return $sentCount;
    }
}
