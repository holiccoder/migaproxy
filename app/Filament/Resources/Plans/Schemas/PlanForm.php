<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->maxLength(2000)
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('currency')
                    ->required()
                    ->length(3)
                    ->dehydrateStateUsing(fn (string $state): string => Str::upper($state)),
                Select::make('interval_unit')
                    ->options([
                        'month' => 'Month',
                        'year' => 'Year',
                    ])
                    ->required()
                    ->default('month'),
                TextInput::make('interval_count')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(12)
                    ->default(1),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
                TextInput::make('provider')
                    ->required()
                    ->maxLength(100)
                    ->default('fake'),
                TextInput::make('provider_price_id')
                    ->maxLength(255),
            ]);
    }
}
