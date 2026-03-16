<?php

namespace App\Actions\Notifications;

use App\Models\Admin;
use App\Models\User;

class SendBulkPrivateMessage
{
    public function execute(?Admin $sender, string $subject, string $message, bool $sendToEmail = false): int
    {
        $recipientCount = User::query()->count();

        if ($recipientCount === 0) {
            return 0;
        }

        SendBulkPrivateMessageJob::dispatch(
            adminId: $sender?->id,
            adminName: $sender?->name,
            subject: $subject,
            message: $message,
            sendToEmail: $sendToEmail,
        );

        return $recipientCount;
    }
}
