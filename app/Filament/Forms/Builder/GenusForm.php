<?php

namespace App\Filament\Forms\Builder;

use App\Filament\Forms\Builder\Contracts\NameField;
use App\Filament\Forms\Builder\Contracts\ParentField;
use App\Filament\Forms\Builder\FormBuilder;
use App\Filament\Forms\Components\BelongsToSelect;
use App\Filament\Forms\Components\ImageUpload;
use App\Filament\Forms\Components\LocalizeRepeater;
use App\Filament\Forms\Components\NameInput;
use Filament\Schemas\Components\Component;

class GenusForm extends FormBuilder implements NameField, ParentField
{
    public static function nameField(): Component
    {
        return NameInput::make()
            ->columnSpanFullWhenFieldMissing('family_id');
    }

    public static function parentField(): Component
    {
        return BelongsToSelect::make('family_id');
    }

    public static function attributeFields(): array
    {
        return [
            ImageUpload::make('image_paths')
                ->multiple()
                ->directory('uploads/images/genera'),

            LocalizeRepeater::make('localizations'),
        ];
    }
}
