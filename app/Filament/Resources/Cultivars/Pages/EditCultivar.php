<?php

namespace App\Filament\Resources\Cultivars\Pages;

use App\Filament\Resources\Cultivars\CultivarResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCultivar extends EditRecord
{
    protected static string $resource = CultivarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->disabled(fn ($record) => $record->is_default)->tooltip(fn ($record) => $record->is_default ? __('Default cultivar records cannot be deleted') : null),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // dd($data);
        return parent::mutateFormDataBeforeSave($data);
    }
}
