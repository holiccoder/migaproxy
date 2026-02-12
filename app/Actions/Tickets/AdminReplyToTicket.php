<?php

namespace App\Actions\Tickets;

use App\Models\Admin;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Notifications\AdminPrivateMessageNotification;

class AdminReplyToTicket
{
    public function execute(Ticket $ticket, ?Admin $admin, string $message, string $status): void
    {
        $ticket->messages()->create([
            'user_id' => null,
            'admin_id' => $admin?->id,
            'sender_type' => TicketMessage::SENDER_ADMIN,
            'message' => $message,
        ]);

        $ticket->forceFill([
            'status' => $status,
            'last_admin_reply_at' => now(),
            'resolved_at' => $status === Ticket::STATUS_RESOLVED ? now() : null,
        ])->save();

        $ticket->user->notify(new AdminPrivateMessageNotification(
            subject: "Ticket #{$ticket->id} update",
            message: $message,
            adminId: $admin?->id,
            adminName: $admin?->name
        ));
    }
}
