<?php

namespace App\Filament\Resources\AffiliateConversions\Pages;

use App\Filament\Resources\AffiliateConversions\AffiliateConversionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateConversion extends EditRecord
{
    protected static string $resource = AffiliateConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
