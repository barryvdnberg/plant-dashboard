<?php

namespace App\Filament\Resources\Plants\RelationManagers\Actions;

use App\Filament\Forms\Components\Tooltip;
use App\Models\Cultivar;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CultivarReplicateAction
{
    public static function make(): ReplicateAction
    {
        return ReplicateAction::make()
            ->schema([
                TextInput::make('new_name')
                    ->label(__('New :name', ['name' => __('attributes.Name')]))
                    ->afterLabel(Schema::start(Tooltip::make(__('The name for the new cultivar. If left empty, the name of the original name will be used.')))),

            ])
            ->beforeReplicaSaved(function (array $data, Cultivar $replica): void {
                if ($data['new_name']) {
                    $replica->name = $data['new_name'];
                } else {
                    // When no name is provided, instead of simply appending - Copy to the name.
                    // Check if the name already exists with the ' - Copy' part if so. Keep checking with ' - Copy (2)', ' - Copy (3)' etc until a unique name is found.
                    $baseName = $replica->name;
                    $newName = $baseName.' - Copy';
                    $counter = 2;
                    while (Cultivar::query()->where('name', $newName)->exists()) {
                        $newName = $baseName." - Copy ({$counter})";
                        $counter++;
                    }
                    $replica->name = $newName;
                }
            })
            ->after(function (Cultivar $replica, Cultivar $record): void {
                $record->replicateRelationsTo($replica);
            });
    }
}
