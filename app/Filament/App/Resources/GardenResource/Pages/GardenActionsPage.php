<?php

namespace App\Filament\App\Resources\GardenResource\Pages;

use App\Filament\App\Resources\GardenResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class GardenActionsPage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = GardenResource::class;

    protected string $view = 'filament.app.resources.garden-resource.pages.garden-actions-page';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
