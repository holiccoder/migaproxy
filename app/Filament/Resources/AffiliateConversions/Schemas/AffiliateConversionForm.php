<?php

namespace App\Filament\Resources\AffiliateConversions\Schemas;

use App\Models\AffiliateConversion;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AffiliateConversionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('affiliate_id')
                    ->relationship('affiliate', 'code')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload(),
                Select::make('order_id')
                    ->relationship('order', 'public_id')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('subscription_id')
                    ->relationship('subscription', 'id')
                    ->searchable()
                    ->preload(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('commission_amount')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Select::make('status')
                    ->required()
                    ->options([
                        AffiliateConversion::STATUS_PENDING => 'Pending',
                        AffiliateConversion::STATUS_APPROVED => 'Approved',
                        AffiliateConversion::STATUS_REJECTED => 'Rejected',
                        AffiliateConversion::STATUS_PAID => 'Paid',
                    ]),
                DateTimePicker::make('approved_at'),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
