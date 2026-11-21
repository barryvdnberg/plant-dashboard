<?php

namespace App\Filament\Resources\Localizables\Pages;

use App\Filament\Resources\Localizables\LocalizableResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLocalizables extends ListRecords
{
    protected static string $resource = LocalizableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
