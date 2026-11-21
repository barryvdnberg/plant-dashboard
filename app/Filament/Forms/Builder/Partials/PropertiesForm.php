<?php

namespace App\Filament\Forms\Builder\Partials;

use App\Enums\GrowthPattern;
use App\Enums\PlantTrait;
use App\Enums\PlantType;
use App\Filament\Forms\Components\HasManySelect;
use App\Filament\Forms\Components\RelationshipSelect;
use App\Filament\Forms\Components\Tooltip;
use App\Filament\Forms\Components\ColoredCheckboxList;
use App\Models\PlantProperties;
use Filament\Forms\Components\CheckboxList;
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
                ->options(PlantTrait::class)
                ->columns(3)
                ->searchable()
                ->bulkToggleable()
                ->afterStateHydrated(function (CheckboxList $component, $record) {
                    if (!$record) return;
                    if (!$record instanceof PlantProperties) return;

                    $component->state(
                        $record->traits()->pluck('trait_index')
                    );
                })
                ->dehydrateStateUsing(fn (array|null $state) => $state ?? [])
                ->saveRelationshipsUsing(function (array $state, $record) {
                    if (!$record) return;
                    if (!$record instanceof PlantProperties) return;

                    $record->traits()->delete();

                    foreach ($state as $trait) {
                        $record->traits()->create([
                            'trait_index' => $trait,
                        ]);
                    }
                })
        ];
    }
}
