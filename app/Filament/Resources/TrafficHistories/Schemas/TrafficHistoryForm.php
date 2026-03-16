<?php

namespace App\Filament\Resources\TrafficHistories\Schemas;

use App\Models\TrafficHistory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TrafficHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('success_count')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('length')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                DateTimePicker::make('request_date')
                    ->required(),
                TextInput::make('total_requests')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('ipmart_id')
                    ->required()
                    ->maxLength(255),
                TextInput::make('provider')
                    ->required()
                    ->default(TrafficHistory::PROVIDER_IPMART)
                    ->maxLength(255),
            ]);
    }
}
