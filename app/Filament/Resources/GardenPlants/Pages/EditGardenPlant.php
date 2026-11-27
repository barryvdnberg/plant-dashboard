<?php

namespace App\Filament\Resources\GardenPlants\Pages;

use App\Filament\Resources\GardenPlants\GardenPlantResource;
use App\Models\Garden;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;

class EditGardenPlant extends EditRecord
{
    protected static string $resource = GardenPlantResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $plant = $this->getRecord();

        if (isset($data['garden_id']) && (int) $data['garden_id'] !== (int) $plant->garden_id) {
            $destination = Garden::query()->findOrFail($data['garden_id']);
            Gate::authorize('moveTo', [$plant, $destination]);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
