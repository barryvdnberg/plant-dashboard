<?php

namespace App\Filament\Forms\Builder;

use App\Filament\Forms\Builder\Contracts\NameField;
use App\Filament\Forms\Builder\FormBuilder;
use App\Filament\Forms\Components\LocalizeRepeater;
use App\Filament\Forms\Components\NameInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

class LanguageForm extends FormBuilder implements NameField
{
    public static function nameField(): Component
    {
        return NameInput::make()->columnSpanFull();
    }

    public static function attributeFields(): array
    {
        return [
            TextInput::make('code'),
            TextInput::make('english_name'),
            LocalizeRepeater::make('localizations')
        ];
    }
}
