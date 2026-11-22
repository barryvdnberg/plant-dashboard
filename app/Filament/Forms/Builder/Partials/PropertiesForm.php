<?php

namespace App\Filament\Forms\Builder\Partials;

use App\Enums\GrowthPattern;
use App\Enums\PlantType;
use App\Filament\Forms\Components\ColoredCheckboxList;
use App\Filament\Forms\Components\HasManySelect;
use App\Filament\Forms\Components\RelationshipSelect;
use App\Filament\Forms\Components\Tooltip;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PropertiesForm
{
    public static function form(): array
    {
        return [
            Select::make('plant_type')
                ->label(__('attributes.Plant_type'))
                ->options(PlantType::class),

            HasManySelect::make('growth_styles')
                ->label(__('attributes.Growth_styles'))
                ->options(GrowthPattern::class)
                ->live()
                ->table('growthStyles', 'name'),

            RelationshipSelect::make('garden_styles')
                ->relationship('gardenStyles', 'name')
                ->multiple()
                ->preload()
                ->afterLabel(Schema::start(Tooltip::make(__('The types of gardens this plant fits well in.')))),

            ColoredCheckboxList::make('plant_traits')
                ->label(__('attributes.Plant_traits'))
                ->relationship('traits', 'trait_index')
                ->columns(3)
                ->searchable()
                ->bulkToggleable(),
        ];
    }
}
