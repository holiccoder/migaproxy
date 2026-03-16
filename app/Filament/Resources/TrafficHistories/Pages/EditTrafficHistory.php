<?php

namespace App\Filament\Resources\TrafficHistories\Pages;

use App\Filament\Resources\TrafficHistories\TrafficHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrafficHistory extends EditRecord
{
    protected static string $resource = TrafficHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
