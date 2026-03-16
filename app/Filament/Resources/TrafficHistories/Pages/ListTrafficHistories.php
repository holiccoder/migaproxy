<?php

namespace App\Filament\Resources\TrafficHistories\Pages;

use App\Filament\Resources\TrafficHistories\TrafficHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListTrafficHistories extends ListRecords
{
    protected static string $resource = TrafficHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
