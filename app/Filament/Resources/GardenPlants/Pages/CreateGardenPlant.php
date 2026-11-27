<?php

namespace App\Filament\Resources\GardenPlants\Pages;

use App\Filament\Resources\GardenPlants\GardenPlantResource;
use App\Models\Garden;
use App\Models\GardenPlant;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Gate;

class CreateGardenPlant extends CreateRecord
{
    protected static string $resource = GardenPlantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $garden = Garden::query()->findOrFail($data['garden_id']);
        Gate::authorize('createIn', [GardenPlant::class, $garden]);

        return $data;
    }
}
