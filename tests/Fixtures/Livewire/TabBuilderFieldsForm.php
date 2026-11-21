<?php

namespace Tests\Fixtures\Livewire;

use App\Filament\Forms\Components\ColorSelect;
use App\Filament\Forms\Components\TabBuilder;
use App\Models\Color;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Tests\Fixtures\Models\TabBuilderRecord;

class TabBuilderFieldsForm extends TabBuilderForm
{
    public bool $standalone = false;

    public bool $singleColor = false;

    public bool $nativeColors = false;

    public bool $customColorRelationship = false;

    public array $colorPivotData = ['tag' => 'default'];

    public bool $usePivotDataClosure = false;

    public string $colorRelationshipName = 'colors';

    public function form(Schema $schema): Schema
    {
        $colors = ColorSelect::make('colors')
            ->pivotData($this->usePivotDataClosure
                ? fn (?Model $record): array => $record?->getAttribute('items')['pivot_data'] ?? $this->colorPivotData
                : $this->colorPivotData)
            ->multiple(! $this->singleColor);

        if ($this->customColorRelationship) {
            $colors->relationship('palette', 'hex_value', fn (Builder $query) => $query->where('colors.name', '!=', 'Green'))->preload();
        } else {
            $colors->relationship($this->colorRelationshipName);
        }

        $colorFields = [
            $colors,
            'spring' => ColorSelect::make('spring_colors')->pivotData(['tag' => 'spring']),
        ];

        if ($this->standalone) {
            return $schema->statePath('data')->model($this->record ?? TabBuilderRecord::class)->components(array_values($colorFields));
        }

        $definitions = [
            Group::make([TextInput::make('name')->required()])->relationship('details'),
            ...$colorFields,
            ...($this->relationshipOnly ? [] : [
                'testguy' => Select::make('test')->options(['first' => 'First', 'second' => 'Second'])->default('first')->required(),
            ]),
            ...($this->nativeColors ? [
                'native_colors' => Select::make('selected_colors')->multiple()
                    ->relationship('colors', 'name')->options(fn () => Color::localizedMap()),
            ] : []),
        ];

        $builder = TabBuilder::make('items')->disabled($this->disabled)->schemas($definitions);

        return $schema->statePath('data')->model($this->record ?? TabBuilderRecord::class)->components([
            $this->nested ? Group::make([$builder])->relationship('details') : $builder,
        ]);
    }
}
