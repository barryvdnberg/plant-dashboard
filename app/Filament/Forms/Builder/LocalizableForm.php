<?php

namespace App\Filament\Forms\Builder;

use App\Filament\Forms\Builder\FormBuilder;
use App\Filament\Forms\Components\NumberInput;
use App\Filament\Forms\Components\BelongsToSelect;
use App\Models\Localizable;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class LocalizableForm extends FormBuilder
{
    public static function attributeFields(): array
    {
        return [
            BelongsToSelect::make('language_id')
                ->required(),
            static::getLocalizableTypeComponent(),
            NumberInput::make('localizable_id')
                ->required(),
            TextInput::make('field'),
            Textarea::make('value')
                ->required()
                ->columnSpanFull()
        ];
    }

    private static function getLocalizableTypeComponent(): Select
    {
        return Select::make('localizable_type')
            ->options(function () {
                $models = Localizable::getImplementedModels();

                $options = [];
                foreach ($models as $model) {
                    $options[$model] = $model;
                }
                return $options;
            })
            ->required()
            ->rules(self::getUniqueCombinationRule());
    }

    private static function getUniqueCombinationRule(): Closure
    {
        return fn ($get): Closure => function (Closure $fail) use ($get) {
            $model = Localizable::query()
                ->where([
                    'localizable_type' => $get('localizable_type'),
                    'localizable_id' => $get('localizable_id'),
                    'field' => $get('field'),
                    'language_id' => $get('language_id')
                ])
                ->where('id', '!=', $get('id'))->get()->first();
            if ($model) {
                $fail('The combination of Localizable Type, Localizable ID, Field, and Language ID must be unique.');
            }
        };
    }

}
