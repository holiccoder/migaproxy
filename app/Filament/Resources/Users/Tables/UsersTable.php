<?php

namespace App\Filament\Resources\Users\Tables;

use App\Actions\Notifications\SendPrivateMessage;
use App\Models\Admin;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('sendPrivateMessage')
                    ->label('Send Message')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->form([
                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('message')
                            ->required()
                            ->maxLength(5000)
                            ->rows(6),
                    ])
                    ->action(function (User $record, array $data, SendPrivateMessage $sendPrivateMessage): void {
                        $authAdmin = auth('admin')->user();
                        $sender = $authAdmin instanceof Admin ? $authAdmin : null;

                        $sendPrivateMessage->execute(
                            recipient: $record,
                            sender: $sender,
                            subject: $data['subject'],
                            message: $data['message'],
                        );

                        FilamentNotification::make()
                            ->title('Private message sent.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
