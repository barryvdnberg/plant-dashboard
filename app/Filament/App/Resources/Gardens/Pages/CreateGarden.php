<?php

namespace App\Filament\App\Resources\Gardens\Pages;

use App\Filament\App\Resources\GardenResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateGarden extends CreateRecord
{
    protected static string $resource = GardenResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                $this->getFormContentComponent(),
            ]);
    }
}
