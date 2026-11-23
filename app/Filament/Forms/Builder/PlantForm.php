<?php

namespace App\Filament\Forms\Builder;

use App\Filament\Forms\Builder\Contracts\NameField;
use App\Filament\Forms\Builder\Contracts\ParentField;
use App\Filament\Forms\Components\BelongsToSelect;
use App\Filament\Forms\Components\ImageUpload;
use App\Filament\Forms\Components\LocalizeRepeater;
use App\Filament\Forms\Components\NameInput;
use App\Filament\Schemas\Schema as HideableSchema;
use App\Models\Species;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class PlantForm extends FormBuilder implements NameField, ParentField
{
    public static function nameField(): NameInput
    {
        return NameInput::make()
            ->columnSpanFullWhenFieldMissing('species_id')
            ->belowContent(self::generateNameSchema());
    }

    public static function parentField(): BelongsToSelect
    {
        return BelongsToSelect::make('species_id')->withParent();
    }

    public static function attributeFields(): array
    {
        return [
            ImageUpload::make('image_paths')
                ->multiple()
                ->directory('uploads/images/plants'),

            LocalizeRepeater::make('localizations'),
        ];
    }

    public static function getBaseCultivarForm(): Group
    {
        return Group::make(
            CultivarForm::attributeFields()
        )
            ->relationship('baseCultivar')
            ->columnSpanFull();
    }

    public static function getCultivarsForm(): Repeater
    {
        $component = Repeater::make('cultivars')
            ->defaultItems(0)
            ->columnSpanFull()
            ->schema([
                CultivarForm::nameField(),
                ...CultivarForm::attributeFields(),
            ])
            ->relationship('cultivars');

        return $component;
    }

    private static function generateNameSchema(): Schema
    {
        return HideableSchema::end([
            Icon::make(Heroicon::InformationCircle),
            Text::make(__('Create :type based on :name', [
                'type' => __('attributes.name'),
                'name' => __('attributes.species')
            ])),
            self::generateNameAction(),
        ])
            ->visibleJs(<<<'JS'
                !!$get('species_id') && !$get('name')
            JS);
    }

    private static function generateNameAction(): Action
    {
        return Action::make('generate')
            ->label(__('Generate'))
            ->action(function (Set $set, Get $get): void {
                $species = Species::find($get('species_id'));

                if (! $species) {
                    return;
                }

                if ($species->genus) {
                    $set('name', $species?->genus->name.' '.$species?->name);
                } else {
                    $set('name', $species?->name);
                }
            });
    }
}
