<?php

namespace App\Filament\Resources\Plants\RelationManagers\Actions;

use App\Filament\Forms\Components\ModelSelects\CultivarSelect;
use App\Filament\Forms\Components\ModelSelects\PlantSelect;
use App\Filament\Resources\Plants\RelationManagers\CultivarsRelationManager;
use App\Models\Cultivar;
use App\Models\Plant;
use Filament\Actions\AssociateAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class CultivarAssociateAction
{
    public static function make(): AssociateAction
    {
        return AssociateAction::make()
            ->preloadRecordSelect()
            ->modelLabel(__('attributes.cultivar'))
            ->recordTitle(fn ($record) => $record->display_name ?? $record->name)
            ->schema(fn (AssociateAction $action): array => [
                CultivarSelect::make('testing'),
                PlantSelect::make('plant_id')
                    ->live()
                    ->label(__('Select :name', ['name' => __('attributes.plant')]))
                    ->modifyQueryUsing(function (Builder $query, Component $livewire) {
                        $currentPlantId = $livewire->ownerRecord->id;
                        return $currentPlantId ? $query->where('plants.id', '<>', $currentPlantId) : $query;
                    })
                    ->required(),
                $action
                    ->getRecordSelect()
                    ->live()
                    ->afterStateUpdated(function($get, $set, $state) {
                        if (!is_int($state)) {
                            return;
                        }
                        
                        $cultivar = Cultivar::query()->find($state);

                        if (!$cultivar) {
                            return;
                        }

                        $plantId = $cultivar->plant_id;

                        if (!$plantId) {
                            return;
                        }

                        $set('plant_id', $plantId);
                    })
            ])
            ->recordSelectOptionsQuery(function (Builder $query, CultivarsRelationManager $livewire, AssociateAction $action) {
                $actionsFlat = $livewire->mountedActions;

                $plantId = null;

                // grab the first action from the $actionsFlat array with the name 'associate' inside the array arg
                if ($actionsFlat && is_array($actionsFlat)) {
                    foreach ($actionsFlat as $actionData) {
                        if ($actionData['name'] === 'associate') {
                            $plantId = $actionData['data']['plant_id'] ?? null;
                            break;
                        }
                    }
                }

                if (!$plantId) return;

                $query->where('plant_id', $plantId);
            });
    }
}
