<?php

namespace App\Filament\Resources\Cultivars\Actions;

use App\Filament\Forms\Components\ModelSelects\PlantSelect;
use App\Filament\Resources\Cultivars\Pages\CreateCultivar;
use App\Filament\Schemas\Components\InlineGroup;
use App\Models\Cultivar;
use App\Models\Plant;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportCultivarAction
{
    public static function make(): Action
    {
        return Action::make('importCultivar')
            ->label(__('Import data'))
            ->modalHeading(__('Import fields from another cultivar'))
            ->icon(Heroicon::ArrowDownTray)
            ->requiresConfirmation()
            ->fillForm(function (CreateCultivar $livewire) {
                Plant::query()->find(43);
                // would also work but always returns a string instead of the actual state type
                // $plantId = $get('/data.plant_id');

                return [
                    'plant_id' => $livewire->form->getComponent('plant_id')->getState(),
                ];
            })
            ->schema(function () {
                return [
                    PlantSelect::make('plant_id')
                        ->label(Plant::getModelLabel())
                        ->required(),

                    Select::make('cultivar_id')
                        ->label(Cultivar::getModelLabel())
                        ->options(function (Get $get) {
                            $plantId = $get('plant_id');
                            if (!$plantId) {
                                return [];
                            }
                            return Cultivar::query()
                                ->where('plant_id', $plantId)
                                // ->leftJoin('')
                                ->pluck('name', 'id');
                        })
                        ->required()
                ];
            })
            ->action(function (array $data, $livewire) {
                $components = $livewire->form->getFlatComponents();

                $groups = [];
                foreach ($components as $key => $component) {
                    if ($component instanceof Group && !$component instanceof InlineGroup) {
                        $groups[$key] = $component;
                    }
                }

                $fields = [];
                foreach ($components as $key => $component) {
                    if ($component instanceof Field) {
                        $fields[$key] = $component;
                    }
                }

                // we only support 1 deep nesting for now
                $fieldArray = self::splitArrayByDotNotation($fields);
                // copying image paths is not supported
                unset($fieldArray['image_paths']);

                $sourceId = $data['cultivar_id'] ?? null;

                if (!$sourceId) {
                    return;
                }

                $source = Cultivar::with([
                    'colors',
                    'properties',
                    'foliage',
                    'environment',
                    'maintenance',
                    'blooming'
                ])
                    ->find($sourceId);

                if (!$source) {
                    return;
                }

                self::bootOverwrites();
                self::resolveStates($fieldArray, $source);

            });
            // ->visible(fn($schemaOperation) => $schemaOperation === 'create');
    }

    protected static array $overwrites = [];

    public static function bootOverwrites(): void
    {
        // TODO: make HasManySelect relationships auto detectable as well
        // now they are all defined here manually due to the value column (e.g. 'index', 'value', 'name')
        self::$overwrites = [
            'maintenance.maintenance_recommendation' => fn ($source) => $source->recommendations?->sortBy('position')->pluck('value')->all(),
            
            'blooming.months' => fn ($source) => $source->months?->pluck('index')->all() ?? [],
            
            'environment.sun_exposures' => fn ($source) => $source->sunExposures?->pluck('value')->all() ?? [],
            
            'properties.growth_styles' => fn ($source) => $source->growthStyles?->pluck('name')->all() ?? [],
            'properties.plant_traits' => fn ($source) => $source->traits?->pluck('trait_index')->all() ?? [],
        ];
    }

    protected static function resolveStates(array $fieldArray, Model $source, string $path = '') {
        foreach ($fieldArray as $key => $value) {
            if ($value instanceof Field) {
                $completeKey = $path ? "$path.$key" : $key;
                // check if the overwrites contains the complete key
                if (array_key_exists($completeKey, self::$overwrites)) {
                    $overwrite = self::$overwrites[$completeKey];

                    if (is_callable($overwrite)) {
                        $state = $overwrite($source);
                    }
                    elseif (is_string($overwrite) && method_exists($source, $overwrite)) {
                        $state = $source->$overwrite();
                    }
                    else {
                        $state = $overwrite;
                    }
                    $value->state($state);
                }
                else {
                    // for now we assume all select fields are relationship fields
                    // this can be improved later, for the not multiple selects
                    // won't be needed on multiple as we don't save arrays in columns to increase performance
                    if ($value instanceof Select) {
                        if ($value->isMultiple()) {
                            $state = $source->{Str::camel($key)}?->pluck('id')->all() ?? [];
                            $value->state($source->{Str::camel($key)}?->pluck('id')->all() ?? []);
                        }
                        else {
                            if ($value->getRelationshipName()) {
                                $value->state($source->{$value->getRelationshipName()}?->id ?? null);
                            }
                            else {
                                $value->state($source->$key);
                            }
                        }
                    }
                    else {
                        // if the relationship name is not empty, we assume it's a relationship field
                        if (method_exists($value, 'getRelationshipName') && $value->getRelationshipName() !== null) {
                            // for relationship fields, we try to get the related model's id(s)
                            $related = $source->{$value->getRelationshipName()};
                            if ($related instanceof Model) {
                                $value->state($related->id);
                            } elseif ($related instanceof \Illuminate\Database\Eloquent\Collection) {
                                $value->state($related->pluck('id')->all());
                            } else {
                                $value->state(null);
                            }
                        } else {
                            // otherwise, we just get the attribute value
                            $value->state($source->$key);
                        }
                    }
                }
            }
            else {
                // check if source containts the key as a relation
                if (!method_exists($source, $key)) {
                    continue;
                }
                if (!$source->$key instanceof Model) {
                    continue;
                }

                // recursive call with updated path f.e.
                // path 'properties' + 'growth_styles' = 'properties.growth_styles'
                // path '' + 'plant_type' = 'plant_type'
                self::resolveStates($value, $source->$key, ($path && $key) ? "$path.$key" : $key);
            }
        }
    }

    /**
     * split keys by dot notation into a nested array
     * f.e. 'properties.growth_styles' and 'properties.plant_type' 
     * becomes [properties' => ['growth_styles' => ..., 'plant_type' => ...]]
     * should work recursive for multiple levels e.g. 'a.b.c.d' -> ['a' => ['b' => ['c' => ['d' => ...]]]]
     */
    protected static function splitArrayByDotNotation(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $keys = explode('.', $key);
            $temp = &$result;

            foreach ($keys as $part) {
                if (!isset($temp[$part])) {
                    $temp[$part] = [];
                }
                $temp = &$temp[$part];
            }

            $temp = $value;
        }

        return $result;
    }
}
