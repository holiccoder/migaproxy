<?php

namespace App\Filament\Resources\Affiliates\Schemas;

use App\Models\Affiliate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AffiliateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->required()
                    ->maxLength(64)
                    ->dehydrateStateUsing(fn (string $state): string => Str::upper(trim($state)))
                    ->unique(ignoreRecord: true),
                Select::make('commission_type')
                    ->options([
                        Affiliate::COMMISSION_TYPE_PERCENTAGE => 'Percentage',
                        Affiliate::COMMISSION_TYPE_FIXED => 'Fixed Amount',
                    ])
                    ->required()
                    ->default(Affiliate::COMMISSION_TYPE_PERCENTAGE),
                TextInput::make('commission_value')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('cookie_days')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(365)
                    ->default(30),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
                TextInput::make('total_earnings')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($state): string => (string) (($state ?? 0) / 100)),
            ]);
    }
}
