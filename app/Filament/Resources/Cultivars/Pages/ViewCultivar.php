<?php

namespace App\Filament\Resources\Cultivars\Pages;

use App\Filament\Resources\Cultivars\CultivarResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCultivar extends ViewRecord
{
    protected static string $resource = CultivarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
