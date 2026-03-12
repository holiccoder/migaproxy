<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('public_id')
                    ->required()
                    ->maxLength(26)
                    ->default(Str::ulid()->toBase32())
                    ->unique(ignoreRecord: true),
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
                Select::make('coupon_id')
                    ->relationship('coupon', 'code')
                    ->searchable()
                    ->preload(),
                TextInput::make('coupon_code')
                    ->maxLength(64),
                Select::make('subscription_id')
                    ->relationship('subscription', 'id')
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->options([
                        Order::STATUS_PENDING => 'Pending',
                        Order::STATUS_PAID => 'Paid',
                        Order::STATUS_FAILED => 'Failed',
                    ])
                    ->required()
                    ->default(Order::STATUS_PENDING),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('discount_total')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('currency')
                    ->required()
                    ->length(3)
                    ->default('USD'),
                KeyValue::make('metadata')
                    ->nullable()
                    ->columnSpanFull(),
                DateTimePicker::make('paid_at'),
                DateTimePicker::make('failed_at'),
            ]);
    }
}
