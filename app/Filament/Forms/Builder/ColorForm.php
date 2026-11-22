<?php

namespace App\Filament\Forms\Builder;

use App\Filament\Forms\Builder\Contracts\NameField;
use App\Filament\Forms\Builder\FormBuilder;
use App\Filament\Forms\Components\NameInput;
use App\Filament\Forms\Components\LocalizeRepeater;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Component;

final class ColorForm extends FormBuilder implements NameField
{        
    public static function nameField(): Component
    {
        return NameInput::make()->columnSpanFull();
    }

    public static function attributeFields(): array
    {
        return [
            LocalizeRepeater::make('localizations'),
            ColorPicker::make('hex_value')
                ->format('hex')
                ->required()
                ->regex('/\A#[0-9a-fA-F]{6}\z/')
                ->columnSpanFull(),
        ];  
    }
}
