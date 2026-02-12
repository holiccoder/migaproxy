<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Actions\Tickets\AdminReplyToTicket;
use App\Models\Admin;
use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('priority')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('last_user_reply_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_admin_reply_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Ticket::STATUS_OPEN => 'Open',
                        Ticket::STATUS_IN_PROGRESS => 'In Progress',
                        Ticket::STATUS_RESOLVED => 'Resolved',
                        Ticket::STATUS_CLOSED => 'Closed',
                    ]),
                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),
            ])
            ->recordActions([
                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->form([
                        Textarea::make('message')
                            ->required()
                            ->maxLength(10000)
                            ->rows(6),
                        Select::make('status')
                            ->required()
                            ->options([
                                Ticket::STATUS_IN_PROGRESS => 'In Progress',
                                Ticket::STATUS_RESOLVED => 'Resolved',
                                Ticket::STATUS_CLOSED => 'Closed',
                                Ticket::STATUS_OPEN => 'Open',
                            ])
                            ->default(Ticket::STATUS_IN_PROGRESS),
                    ])
                    ->action(function (Ticket $record, array $data, AdminReplyToTicket $adminReplyToTicket): void {
                        $authAdmin = auth('admin')->user();
                        $admin = $authAdmin instanceof Admin ? $authAdmin : null;

                        $adminReplyToTicket->execute(
                            ticket: $record,
                            admin: $admin,
                            message: $data['message'],
                            status: $data['status'],
                        );

                        Notification::make()
                            ->title('Reply sent to ticket.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
