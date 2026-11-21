<?php

namespace App\Filament\Forms\Builder;

use App\Filament\Forms\Builder\Contracts\NameField;
use App\Filament\Forms\Builder\FormBuilder;
use App\Filament\Forms\Components\BelongsToSelect;
use App\Filament\Forms\Components\NameInput;
use App\Filament\Forms\Components\NumberInput;
use Filament\Schemas\Components\Component;

class GardenPlantForm extends FormBuilder implements NameField
{
    public static function nameField(): Component
    {
        return NameInput::make();
    }

    public static function attributeFields(): array
    {
        return [
            BelongsToSelect::make('garden_id'),
            BelongsToSelect::make('cultivar_id'),
            NumberInput::make('size')
                ->minValue(0)
                ->maxValue(999999.99)
        ];
    }
}
