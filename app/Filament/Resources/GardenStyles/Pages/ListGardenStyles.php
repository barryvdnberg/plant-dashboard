<?php

namespace App\Filament\Resources\GardenStyles\Pages;

use App\Filament\Resources\GardenStyles\GardenStyleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGardenStyles extends ListRecords
{
    protected static string $resource = GardenStyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
