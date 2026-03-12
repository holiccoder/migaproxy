<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Models\Subscription;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('plan_id')
                    ->relationship('plan', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('order_id')
                    ->relationship('order', 'public_id')
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->options([
                        Subscription::STATUS_ACTIVE => 'Active',
                        Subscription::STATUS_PAST_DUE => 'Past Due',
                        Subscription::STATUS_CANCELED => 'Canceled',
                    ])
                    ->required()
                    ->default(Subscription::STATUS_ACTIVE),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('ends_at'),
                DateTimePicker::make('canceled_at'),
            ]);
    }
}
