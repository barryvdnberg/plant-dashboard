<?php

namespace App\Filament\Resources\Genera\Pages;

use App\Filament\Resources\Genera\GenusResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;

class EditGenus extends EditRecord
{
    protected static string $resource = GenusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
