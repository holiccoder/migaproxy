<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('traffic')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('description')
                    ->required()
                    ->columnSpanFull(),
                KeyValue::make('features')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('days')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(30),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}
