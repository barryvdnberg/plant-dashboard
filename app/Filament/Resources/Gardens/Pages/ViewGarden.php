<?php

namespace App\Filament\Resources\Gardens\Pages;

use App\Filament\Resources\Gardens\GardenResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGarden extends ViewRecord
{
    protected static string $resource = GardenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
