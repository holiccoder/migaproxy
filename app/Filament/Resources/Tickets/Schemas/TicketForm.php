<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Models\Ticket;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    })
                    ->columnSpanFull(),
            ]);
    }
}
