<?php

namespace App\Filament\Forms\Builder;

use App\Filament\Forms\Builder\Contracts\NameField;
use App\Filament\Forms\Components\BelongsToSelect;
use App\Filament\Forms\Components\NameInput;
use App\Filament\Forms\Components\NumberInput;
use App\Models\Cultivar;
use App\Models\Garden;
use Filament\Schemas\Components\Component;

final class GardenPlantForm extends FormBuilder implements NameField
{
    public static function nameField(): Component
    {
        return NameInput::make();
    }

    public static function attributeFields(): array
    {
        return [
            BelongsToSelect::make('garden_id')
                ->required()
                ->exists(Garden::class, 'id')
                // Save the foreign key only after the page authorizes the destination.
                ->saveRelationshipsUsing(null),

            BelongsToSelect::make('cultivar_id')
                ->required()
                ->exists(Cultivar::class, 'id'),

            NumberInput::make('size')
                ->minValue(0)
                ->maxValue(999999.99),
        ];
    }
}
