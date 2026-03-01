<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Actions\Tickets\AdminReplyToTicket;
use App\Models\Admin;
use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                        Placeholder::make('interaction_history')
                            ->label('Interaction History')
                            ->content(function (Ticket $record): HtmlString {
                                $messages = $record->messages()
                                    ->with(['user:id,name', 'admin:id,name'])
                                    ->oldest()
                                    ->get();

                                if ($messages->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">No interaction history yet.</p>');
                                }

                                $items = $messages
                                    ->map(function ($message): string {
                                        $isAdmin = $message->sender_type === 'admin';
                                        $senderName = $isAdmin
                                            ? ($message->admin?->name ?: 'Admin')
                                            : ($message->user?->name ?: 'User');
                                        $senderLabel = $isAdmin ? 'Admin' : 'User';
                                        $createdAt = $message->created_at?->format('M d, Y H:i') ?? '-';
                                        $safeMessage = nl2br(e($message->message));
                                        $wrapperStyle = $isAdmin ? 'text-align: right; margin-bottom: 12px;' : 'text-align: left; margin-bottom: 12px;';
                                        $bubbleStyle = $isAdmin
                                            ? 'display: inline-block; max-width: 85%; border-radius: 16px 16px 6px 16px; padding: 10px 12px; background: #2563eb; color: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.08);'
                                            : 'display: inline-block; max-width: 85%; border-radius: 16px 16px 16px 6px; padding: 10px 12px; background: #ffffff; color: #1f2937; border: 1px solid #d1d5db; box-shadow: 0 1px 2px rgba(0,0,0,0.06);';
                                        $metaStyle = $isAdmin
                                            ? 'display: flex; justify-content: space-between; gap: 8px; margin-bottom: 6px; font-size: 11px; color: #dbeafe;'
                                            : 'display: flex; justify-content: space-between; gap: 8px; margin-bottom: 6px; font-size: 11px; color: #6b7280;';
                                        $messageStyle = $isAdmin
                                            ? 'font-size: 14px; line-height: 1.55; color: #ffffff;'
                                            : 'font-size: 14px; line-height: 1.55; color: #374151;';

                                        return <<<HTML
<article style="{$wrapperStyle}">
    <div style="{$bubbleStyle}">
        <header style="{$metaStyle}">
            <p style="font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; margin: 0;">{$senderLabel}: {$senderName}</p>
            <p>{$createdAt}</p>
        </header>
        <div style="{$messageStyle}">{$safeMessage}</div>
    </div>
</article>
HTML;
                                    })
                                    ->implode('');

                                return new HtmlString(
                                    '<div style="max-height: 18rem; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 12px; background: #f9fafb; padding: 12px;">'.$items.'</div>'
                                );
                            })
                            ->columnSpanFull(),
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
