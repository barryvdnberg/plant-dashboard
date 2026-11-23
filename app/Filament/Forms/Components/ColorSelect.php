<?php

namespace App\Filament\Forms\Components;

use App\Models\Color;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Support\Services\RelationshipJoiner;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LogicException;

class ColorSelect extends Select
{
    protected string $view = 'filament.forms.components.color-select';

    protected function setUp(): void
    {
        parent::setUp();

        $this->multiple();
        $this->relationship();
        $this->dehydrated(false);
        $this->default(static fn (self $component): array|null => $component->isMultiple() ? [] : null);
        $this->label(static fn (self $component): string => $component->isMultiple() ? Color::getPluralModelLabel() : Color::getModelLabel());

        $this->loadStateFromRelationshipsUsing(static function (self $component): void {
            $component->fillStateFromRelationship();
        });

        $this->saveRelationshipsUsing(static function (self $component): void {
            $component->saveStateToRelationship();
        });
    }

    public function relationship(string|Closure|null $name = null, string|Closure|null $titleAttribute = null, ?Closure $modifyQueryUsing = null, bool $ignoreRecord = false): static
    {
        $this->relationship = $name ?? 'colors';
        $this->relationshipTitleAttribute = $titleAttribute ?? 'name';
        $this->modifyRelationshipQueryUsing = $modifyQueryUsing;
        $this->shouldIgnoreRelationshipRecord = $ignoreRecord;
        $this->cachedRelationshipRecords = null;
        $this->cachedRelationshipOptions = null;

        $usesCatalog = ($this->relationship === 'colors')
            && ($this->relationshipTitleAttribute === 'name')
            && ! $modifyQueryUsing && ! $ignoreRecord;

        // Replace all four callbacks when switching configuration, so catalog
        // options and custom relationship queries cannot leak into each other.
        $this->options(static function (ColorSelect $component) use ($usesCatalog): ?array {
            if (! $usesCatalog) {
                return $component->getOptionsFromRelationship();
            }

            return Color::localizedMap()->all();
        });

        $this->getSearchResultsUsing(static function (ColorSelect $component, ?string $search) use ($usesCatalog): array {
            if (! $usesCatalog) {
                return $component->getSearchResultsFromRelationship($search);
            }

            return Color::localizedMap()
                ->filter(static fn ($label): bool => Str::contains($label, $search ?? '', ignoreCase: true))
                ->all();
        });

        $this->getOptionLabelUsing(static function (ColorSelect $component, mixed $value) use ($usesCatalog): ?string {
            if (! $usesCatalog) {
                return $component->getRelationshipOptionLabels(Arr::wrap($value))[$value] ?? null;
            }

            return Color::localizedMap()->get($value);
        });

        $this->getOptionLabelsUsing(static function (ColorSelect $component, array $values) use ($usesCatalog): array {
            if (! $usesCatalog) {
                return $component->getRelationshipOptionLabels($values);
            }

            return Color::localizedMap()->only($values)->all();
        });

        return $this;
    }

    public function getRelationship(): ?BelongsToMany
    {
        if (! $this->getModelInstance()) {
            return null;
        }

        $relationship = parent::getRelationship();

        if (! ($relationship instanceof BelongsToMany)) {
            throw new LogicException('ColorSelect requires a BelongsToMany or MorphToMany relationship.');
        }

        foreach ($this->getPivotData() as $column => $value) {
            $relationship->wherePivot($column, $value);
        }

        return $relationship;
    }

    public function fillStateFromRelationship(): void
    {
        $relationship = $this->getRelationship();

        if ($relationship === null) {
            $this->state($this->isMultiple() ? [] : null);

            return;
        }

        if ($this->modifyRelationshipQueryUsing) {
            $this->evaluate($this->modifyRelationshipQueryUsing, [
                'query' => $relationship->getQuery(),
                'search' => null,
            ]);
        }

        $ids = $relationship->pluck($relationship->getRelated()->qualifyColumn($relationship->getRelatedKeyName()))
            ->map(static fn ($id): int => (int) $id)->all();

        // Query the scoped relationship even when the unscoped relation has
        // been eager loaded, so selections from other pivot scopes stay separate.
        $this->state($this->isMultiple() ? $ids : ($ids[0] ?? null));
    }

    /**
     * Fetch selected labels independently of option limits and preload settings.
     *
     * @param  array<int, int|string>  $values
     * @return array<int|string, string>
     */
    protected function getRelationshipOptionLabels(array $values): array
    {
        if ($values === []) {
            return [];
        }

        $relationship = Relation::noConstraints(fn () => $this->getRelationship());

        if ($relationship === null) {
            return [];
        }

        $query = app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);
        $query->whereIn($this->getQualifiedRelatedKeyNameForRelationship($relationship), $values);

        if ($this->modifyRelationshipQueryUsing) {
            $query = $this->evaluate($this->modifyRelationshipQueryUsing, ['query' => $query, 'search' => null]) ?? $query;
        }

        return $query->get()->mapWithKeys(fn ($record): array => [
            $record->getKey() => $this->hasOptionLabelFromRecordUsingCallback()
                ? $this->getOptionLabelFromRecord($record)
                : data_get($record, str_replace('->', '.', $this->getRelationshipTitleAttribute())),
        ])->all();
    }
}
