<?php

namespace App\Filament\Resources\Gardens\Pages;

use App\Filament\Resources\Gardens\GardenResource;
use App\Models\Garden;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Gate;

class CreateGarden extends CreateRecord
{
    protected static string $resource = GardenResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] ??= auth()->id();

        if ((int) $data['user_id'] !== (int) auth()->id()) {
            Gate::authorize('assignOwner', Garden::class);
        }

        return $data;
    }
}
