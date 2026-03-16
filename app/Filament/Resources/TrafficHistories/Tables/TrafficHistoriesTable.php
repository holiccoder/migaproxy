<?php

namespace App\Filament\Resources\TrafficHistories\Tables;

use App\Models\TrafficHistory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TrafficHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('ipmart_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('provider')
                    ->badge()
                    ->sortable(),
                TextColumn::make('success_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_requests')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('length')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('request_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('provider')
                    ->options([
                        TrafficHistory::PROVIDER_IPMART => 'IPmart',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
