<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->maxLength(64)
                    ->dehydrateStateUsing(fn (string $state): string => Str::upper(trim($state)))
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        Coupon::TYPE_PERCENTAGE => 'Percentage',
                        Coupon::TYPE_FIXED => 'Fixed Amount',
                    ])
                    ->required()
                    ->default(Coupon::TYPE_PERCENTAGE),
                TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
                TextInput::make('usage_limit')
                    ->numeric()
                    ->minValue(1),
                TextInput::make('used_count')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
            ]);
    }
}
