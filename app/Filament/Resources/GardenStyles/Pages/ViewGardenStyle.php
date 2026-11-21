<?php

namespace App\Filament\Resources\GardenStyles\Pages;

use App\Filament\Resources\GardenStyles\GardenStyleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGardenStyle extends ViewRecord
{
    protected static string $resource = GardenStyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
