<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Cultivars\CultivarResource;
use App\Filament\Resources\Families\FamilyResource;
use App\Filament\Resources\Genera\GenusResource;
use App\Filament\Resources\Plants\PlantResource;
use App\Filament\Resources\Species\SpeciesResource;
use App\Models\Cultivar;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\Species;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';
    protected function getStats(): array
    {
        return [
            Stat::make(FamilyResource::getPluralModelLabel(), Family::count()),
            Stat::make(GenusResource::getPluralModelLabel(), Genus::count()),
            Stat::make(SpeciesResource::getPluralModelLabel(), Species::count()),
            Stat::make(PlantResource::getPluralModelLabel(), Plant::count()),
            Stat::make(CultivarResource::getPluralModelLabel(), Cultivar::count())
        ];
    }
}
