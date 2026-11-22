<?php

namespace App\Filament\Forms\Builder\Partials;

use App\Enums\GroundNutrition;
use App\Enums\HardinessZone;
use App\Enums\Moisture;
use App\Enums\SunExposure;
use App\Filament\Forms\Components\HasManySelect;
use App\Filament\Forms\Components\Tooltip;
use App\Models\SoilType;
use App\Support\Query\Enums\SelectMode;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Slider\Enums\PipsMode;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

final class EnvironmentForm
{
    public static function form(): array
    {
        return [
            CheckboxList::make('soil_types')
                ->label(SoilType::getModelLabel())
                ->relationship('soilTypes', 'name', modifyQueryUsing: function (Builder $query) {
                    $query
                        ->withLocalization()
                        ->addLocalizationCoalesce(SelectMode::OnlyKeepId);
                })
                ->getOptionLabelFromRecordUsing(fn (SoilType $record): string => $record->_label)
                ->columns(3),

            Select::make('winter_hardiness')
                ->label(__('attributes.Winter_hardiness'))
                ->options(HardinessZone::class)
                ->afterLabel(Schema::start(Tooltip::make(__('The USDA hardiness zone where this plant can reliably survive winter.')))),
            
            HasManySelect::make('sun_exposures')
                ->label(__('attributes.Sun_exposure'))
                ->options(SunExposure::class)
                ->table(name: 'sunExposures', valueColumn: 'value'),

            Slider::make('ph_range')
                ->label(__('attributes.Ph_range'))
                ->range(minValue: 0, maxValue: 14)
                ->default([6, 8])
                ->decimalPlaces(1)
                ->step(0.5)
                ->tooltips()
                ->pips(PipsMode::Count)
                ->pipsValues(15)
                ->fillTrack([false, true, false]),

            Select::make('ground_nutrition_minimum')
                ->label(__('attributes.Ground_nutrition_minimum'))
                ->options(GroundNutrition::class),

            Select::make('soil_moisture')
                ->label(__('attributes.Soil_moisture'))
                ->options(Moisture::class),
        ];
    }
}
