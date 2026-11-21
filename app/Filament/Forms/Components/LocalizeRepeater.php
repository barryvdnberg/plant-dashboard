<?php

namespace App\Filament\Forms\Components;

use App\Models\Language;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class LocalizeRepeater extends Repeater
{
    public static function make(?string $name = null): static
    {
        if ($name === null) {
            $name = 'localizations';
        }

        $component = parent::make($name);

        $component->relationship();
        $component->reorderable();

        return $component;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->label(__('attributes.Localizations'));
        $this->columnSpanFull();
        $this->compact();
        $this->deleteAction(fn (Action $action) => $action->requiresConfirmation());
        $this->table([
            TableColumn::make(__('attributes.Language'))
                ->markAsRequired(),
            TableColumn::make(__('attributes.Translation'))
                ->markAsRequired(),
            TableColumn::make(__('attributes.Synonyms')),
        ]);

        $this->schema([
            Hidden::make('field')->default('name'),
            Select::make('language_id')
                ->required()
                ->options(fn () => $this->languageOptions())
                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
            TextInput::make('value')->required(),
            TextInput::make('synonyms'),
        ]);
        $this->default(function () {
            $defaultLanguageIds = Language::query()
                ->whereIn('code', ['nl', 'en'])
                ->pluck('id')
                ->values()
                ->toArray();

            return collect($defaultLanguageIds)
                ->map(fn ($id) => ['language_id' => $id])
                ->toArray();
        });
        $this->orderColumn();
    }

    protected function languageOptions()
    {
        $query = Language::query()
            ->with('localizations');

        return $query
            ->get()
            ->mapWithKeys(fn ($language) => [
                $language->id => $language->name == $language->translated_name
                    ? "{$language->name}"
                    : "{$language->name} ({$language->translated_name})",
            ])
            ->toArray();
    }
}
