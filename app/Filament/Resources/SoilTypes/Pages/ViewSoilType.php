<?php

namespace App\Filament\Resources\SoilTypes\Pages;

use App\Filament\Resources\SoilTypes\SoilTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSoilType extends ViewRecord
{
    protected static string $resource = SoilTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
