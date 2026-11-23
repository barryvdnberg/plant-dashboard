<?php

namespace App\Filament\Resources\Plants\Pages;

use App\Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Plants\PlantResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;

class EditPlant extends EditRecord
{
    protected static string $resource = PlantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
