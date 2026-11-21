<?php

namespace App\Filament\Resources\GardenPlants\Pages;

use App\Filament\Resources\GardenPlants\GardenPlantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGardenPlants extends ListRecords
{
    protected static string $resource = GardenPlantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
