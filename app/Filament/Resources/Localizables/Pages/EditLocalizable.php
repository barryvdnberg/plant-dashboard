<?php

namespace App\Filament\Resources\Localizables\Pages;

use App\Filament\Resources\Localizables\LocalizableResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLocalizable extends EditRecord
{
    protected static string $resource = LocalizableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
