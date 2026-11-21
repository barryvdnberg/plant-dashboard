<?php

namespace App\Filament\Resources\Cultivars\Pages;

use App\Filament\Resources\Cultivars\Actions\ImportCultivarAction;
use App\Filament\Resources\Cultivars\CultivarResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCultivar extends CreateRecord
{
    protected static string $resource = CultivarResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }


    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCreateAnotherFormAction(),
            $this->getCancelFormAction(),   
            ImportCultivarAction::make()
        ];
    }
}
