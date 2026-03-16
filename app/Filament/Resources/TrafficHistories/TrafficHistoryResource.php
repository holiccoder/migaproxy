<?php

namespace App\Filament\Resources\TrafficHistories;

use App\Filament\Resources\TrafficHistories\Pages\CreateTrafficHistory;
use App\Filament\Resources\TrafficHistories\Pages\EditTrafficHistory;
use App\Filament\Resources\TrafficHistories\Pages\ListTrafficHistories;
use App\Filament\Resources\TrafficHistories\Schemas\TrafficHistoryForm;
use App\Filament\Resources\TrafficHistories\Tables\TrafficHistoriesTable;
use App\Models\TrafficHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TrafficHistoryResource extends Resource
{
    protected static ?string $model = TrafficHistory::class;

    protected static string|UnitEnum|null $navigationGroup = 'Billing';

    protected static ?string $recordTitleAttribute = 'ipmart_id';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    public static function form(Schema $schema): Schema
    {
        return TrafficHistoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrafficHistoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrafficHistories::route('/'),
            'create' => CreateTrafficHistory::route('/create'),
            'edit' => EditTrafficHistory::route('/{record}/edit'),
        ];
    }
}
