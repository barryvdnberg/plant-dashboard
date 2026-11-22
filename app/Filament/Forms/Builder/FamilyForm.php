<?php

namespace App\Filament\Forms\Builder;

use App\Filament\Forms\Builder\Contracts\NameField;
use App\Filament\Forms\Builder\FormBuilder;
use App\Filament\Forms\Components\ImageUpload;
use App\Filament\Forms\Components\LocalizeRepeater;
use App\Filament\Forms\Components\NameInput;
use Filament\Schemas\Components\Component;

final class FamilyForm extends FormBuilder implements NameField
{
    public static function nameField(): Component
    {
        return NameInput::make()->columnSpanFull();
    }

    public static function attributeFields(): array
    {
        return [
            ImageUpload::make('image_paths')
                ->multiple()
                ->directory('uploads/images/families'),
            
            LocalizeRepeater::make('localizations')
        ];
    }
}
