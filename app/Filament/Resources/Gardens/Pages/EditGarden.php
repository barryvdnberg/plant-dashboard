<?php

namespace App\Filament\Resources\Gardens\Pages;

use App\Filament\Resources\Gardens\Actions\GetGardenInfoAction;
use App\Filament\Resources\Gardens\GardenResource;
use App\Models\Garden;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;

class EditGarden extends EditRecord
{
    protected static string $resource = GardenResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('user_id', $data)
            && (int) $data['user_id'] !== (int) $this->getRecord()->user_id) {
            Gate::authorize('assignOwner', Garden::class);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [GetGardenInfoAction::make(), ...parent::getFormActions()];
    }
}
