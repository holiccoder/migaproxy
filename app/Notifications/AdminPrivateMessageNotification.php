<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPrivateMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $subject,
        public string $message,
        public ?int $adminId = null,
        public ?string $adminName = null,
        public bool $sendToEmail = false,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->sendToEmail) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject($this->subject)
            ->line($this->message);

        if (filled($this->adminName)) {
            $mailMessage->line("Sent by {$this->adminName}.");
        }

        return $mailMessage;
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'subject' => $this->subject,
            'message' => $this->message,
            'admin_id' => $this->adminId,
            'admin_name' => $this->adminName,
            'sent_at' => now()->toIso8601String(),
        ];
    }
}
