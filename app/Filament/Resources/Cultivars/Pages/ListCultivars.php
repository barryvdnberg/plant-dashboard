<?php

namespace App\Filament\Resources\Cultivars\Pages;

use App\Filament\Resources\Cultivars\CultivarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCultivars extends ListRecords
{
    protected static string $resource = CultivarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
