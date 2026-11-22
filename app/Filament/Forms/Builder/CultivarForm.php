<?php

namespace App\Filament\Forms\Builder;

use App\Enums\FormOrder;
use App\Filament\Forms\Builder\Contracts\NameField;
use App\Filament\Forms\Builder\Contracts\ParentField;
use App\Filament\Forms\Builder\Partials\BloomingForm;
use App\Filament\Forms\Builder\Partials\EnvironmentForm;
use App\Filament\Forms\Builder\Partials\FoliageForm;
use App\Filament\Forms\Builder\Partials\MaintenanceForm;
use App\Filament\Forms\Builder\Partials\PropertiesForm;
use App\Filament\Forms\Components\BelongsToSelect;
use App\Filament\Forms\Components\ColorSelect;
use App\Filament\Forms\Components\ImageUpload;
use App\Filament\Forms\Components\LocalizeRepeater;
use App\Filament\Forms\Components\NameInput;
use App\Filament\Schemas\Components\RelationshipTabs\RelationshipTab;
use App\Filament\Schemas\Components\SmartTabs as Tabs;
use App\Models\Cultivar;
use App\Models\Foliage;
use App\Models\PlantBlooming;
use App\Models\PlantEnvironment;
use App\Models\PlantMaintenance;
use App\Models\PlantProperties;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Arr;

class CultivarForm extends FormBuilder implements NameField, ParentField
{
    public static FormOrder $order = FormOrder::PARENT_FIRST;

    public static function nameField(): Component
    {
        return NameInput::make()
            ->required(false)
            ->columnSpanFull()
            ->hidden(fn (?Cultivar $record): bool => $record?->is_default ?? false)
            ->disabled(fn (?Cultivar $record): bool => $record?->is_default ?? false)
            // can be required as it will be filled for non-default records
            ->required();
    }

    public static function parentField(): Component
    {
        return BelongsToSelect::make('plant_id')
            ->columnSpanFull()
            ->disabled(fn (?Cultivar $record): bool => $record?->is_default ?? false);
    }

    public static function attributeFields(): array
    {
        return [
            ImageUpload::make('image_paths')
                ->multiple()
                ->directory('uploads/images/cultivars'),

            ColorSelect::make('colors')
                ->columnSpanFull(),

            Tabs::make([
                RelationshipTab::make()
                    ->label(PlantProperties::getModelLabel())
                    ->schema(PropertiesForm::form())
                    ->relationship('properties', fn (?array $state): bool => self::hasFilledFields($state)),
                RelationshipTab::make()
                    ->label(Foliage::getModelLabel())
                    ->schema(FoliageForm::form())
                    ->relationship('foliage', fn (?array $state): bool => self::hasFilledFields($state)),
                RelationshipTab::make()
                    ->label(PlantEnvironment::getModelLabel())
                    ->schema(EnvironmentForm::form())
                    ->relationship('environment', function (?array $state): bool {
                        $state ??= [];
                        CultivarForm::parsePhFieldDefault($state);

                        return CultivarForm::hasFilledFields($state);
                    }),
                RelationshipTab::make()
                    ->label(PlantBlooming::getModelLabel())
                    ->schema(BloomingForm::form())
                    ->relationship('blooming', fn (?array $state): bool => self::hasFilledFields($state)),
                Tab::make()
                    ->label(PlantMaintenance::getModelLabel())
                    // relationship is handled within the MaintenanceForm repeater
                    ->schema(MaintenanceForm::form()),
                Tab::make()
                    ->label(__('attributes.Localizations'))
                    ->schema([LocalizeRepeater::make('localizations')->default([])])

            ])
                ->key('cultivars-tabs')
                ->lazyLoaded()
                ->columnSpanFull()
                ->persistTabInQueryString(fn (Tabs $component) => $component->getKey()),
        ];
    }

    private static function hasFilledFields(mixed $state): bool
    {
        if (! is_array($state)) {
            return false;
        }

        return filled(array_filter(
            Arr::except($state, ['id', 'cultivar_id', 'created_at', 'updated_at'])
        ));
    }

    private static function parsePhFieldDefault(array &$state)
    {
        if (($state['ph_range'] ?? null) == [6.0, 8.0]) {
            $state['ph_range'] = null;
        }
    }
}
