<?php

namespace App\Filament\Resources\Affiliates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AffiliatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Owner')
                    ->searchable(),
                TextColumn::make('commission_type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('commission_value')
                    ->sortable(),
                TextColumn::make('cookie_days')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('total_earnings')
                    ->label('Earnings')
                    ->money('USD', divideBy: 100)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('commission_type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                    ]),
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
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
