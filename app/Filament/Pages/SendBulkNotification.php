<?php

namespace App\Filament\Pages;

use App\Actions\Notifications\SendBulkPrivateMessage;
use App\Models\Admin;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class SendBulkNotification extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Send Bulk Notification';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.send-bulk-notification';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                Textarea::make('message')
                    ->required()
                    ->maxLength(5000)
                    ->rows(8),
            ])
            ->statePath('data');
    }

    public function sendBulkNotification(SendBulkPrivateMessage $sendBulkPrivateMessage): void
    {
        $validated = $this->form->getState();

        $authAdmin = auth('admin')->user();
        $sender = $authAdmin instanceof Admin ? $authAdmin : null;

        $sentCount = $sendBulkPrivateMessage->execute(
            sender: $sender,
            subject: $validated['subject'],
            message: $validated['message'],
        );

        $this->form->fill();

        Notification::make()
            ->title("Notification sent to {$sentCount} user(s).")
            ->success()
            ->send();
    }
}
