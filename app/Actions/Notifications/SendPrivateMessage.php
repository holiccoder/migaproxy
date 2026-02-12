<?php

namespace App\Actions\Notifications;

use App\Models\Admin;
use App\Models\User;
use App\Notifications\AdminPrivateMessageNotification;

class SendPrivateMessage
{
    public function execute(User $recipient, ?Admin $sender, string $subject, string $message): void
    {
        $recipient->notify(new AdminPrivateMessageNotification(
            subject: $subject,
            message: $message,
            adminId: $sender?->id,
            adminName: $sender?->name
        ));
    }
}
