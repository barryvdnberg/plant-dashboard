<?php

namespace App\Filament\Resources\GardenPlants\Pages;

use App\Filament\Resources\GardenPlants\GardenPlantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGardenPlant extends EditRecord
{
    protected static string $resource = GardenPlantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
