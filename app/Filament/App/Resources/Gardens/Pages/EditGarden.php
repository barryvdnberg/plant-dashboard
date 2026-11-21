<?php

namespace App\Filament\App\Resources\Gardens\Pages;

use App\Filament\App\Resources\GardenResource;
use App\Filament\Resources\Gardens\RelationManagers\PlantsRelationManager;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditGarden extends EditRecord
{
    protected static string $resource = GardenResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['user_id']);

        return $data;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                $this->getFormContentComponent(),
                $this->getRelationManagersContentComponent(),
            ]);
    }

    public function getRelationManagers(): array
    {
        return [PlantsRelationManager::class];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
