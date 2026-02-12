<?php

namespace App\Filament\Resources\AffiliateConversions;

use App\Filament\Resources\AffiliateConversions\Pages\EditAffiliateConversion;
use App\Filament\Resources\AffiliateConversions\Pages\ListAffiliateConversions;
use App\Filament\Resources\AffiliateConversions\Schemas\AffiliateConversionForm;
use App\Filament\Resources\AffiliateConversions\Tables\AffiliateConversionsTable;
use App\Models\AffiliateConversion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AffiliateConversionResource extends Resource
{
    protected static ?string $model = AffiliateConversion::class;

    protected static string|UnitEnum|null $navigationGroup = 'Billing';

    protected static ?string $recordTitleAttribute = 'id';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    public static function form(Schema $schema): Schema
    {
        return AffiliateConversionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AffiliateConversionsTable::configure($table);
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
            'index' => ListAffiliateConversions::route('/'),
            'edit' => EditAffiliateConversion::route('/{record}/edit'),
        ];
    }
}
