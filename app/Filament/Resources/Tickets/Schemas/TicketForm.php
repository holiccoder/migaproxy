<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Models\Ticket;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Group::make([
                            Placeholder::make('interaction_history')
                                ->label('Interaction History')
                                ->content(function ($record): HtmlString {
                                    if (! $record) {
                                        return new HtmlString('<p style="font-size: 14px; color: #6b7280;">No interaction history yet.</p>');
                                    }

                                    $messages = $record->messages()
                                        ->with(['user:id,name', 'admin:id,name'])
                                        ->oldest()
                                        ->get();

                                    if ($messages->isEmpty()) {
                                        return new HtmlString('<p style="font-size: 14px; color: #6b7280;">No interaction history yet.</p>');
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
                                                ? 'display: inline-block; max-width: 90%; border-radius: 16px 16px 6px 16px; padding: 10px 12px; background: #2563eb; color: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.08);'
                                                : 'display: inline-block; max-width: 90%; border-radius: 16px 16px 16px 6px; padding: 10px 12px; background: #ffffff; color: #1f2937; border: 1px solid #d1d5db; box-shadow: 0 1px 2px rgba(0,0,0,0.06);';
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
                                        '<div style="max-height: 70vh; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 12px; background: #f9fafb; padding: 12px;">'.$items.'</div>'
                                    );
                                }),
                        ])->columnSpan(1),
                        Group::make([
                            TextInput::make('subject')
                                ->disabled()
                                ->dehydrated(false),
                            Placeholder::make('user_email')
                                ->label('User Email')
                                ->content(fn ($record): string => $record?->user?->email ?? '-'),
                            Select::make('priority')
                                ->options([
                                    'low' => 'Low',
                                    'medium' => 'Medium',
                                    'high' => 'High',
                                ])
                                ->required(),
                            Select::make('status')
                                ->options([
                                    Ticket::STATUS_OPEN => 'Open',
                                    Ticket::STATUS_IN_PROGRESS => 'In Progress',
                                    Ticket::STATUS_RESOLVED => 'Resolved',
                                    Ticket::STATUS_CLOSED => 'Closed',
                                ])
                                ->required(),
                            Placeholder::make('latest_message')
                                ->label('Latest Message')
                                ->content(function ($record): string {
                                    if (! $record) {
                                        return '-';
                                    }

                                    $latestMessage = $record->messages()->latest()->first();

                                    return $latestMessage?->message ?? '-';
                                }),
                        ])->columnSpan(1),
                    ]),
            ]);
    }
}
