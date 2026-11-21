<?php

namespace App\Models\Concerns;

use App\Models\Contracts\Localization;
use App\Support\Query\Enums\SelectMode;
use App\Support\Query\JoinHelper;
use App\Support\Query\SelectHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Generic helpers to compose join/select/localization behavior for belongs-to properties.
 *
 * Localization is optional and is only applied when the related model implements
 * the Localization contract.
 *
 * @mixin Model
 *
 * @phpstan-require-extends Model
 *
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait HasBelongsToProperties
{
    use JoinsBelongsToPaths;
    use SelectsLocalization;

    protected function resolveBelongsToPropertyPathAlias(mixed $alias, string $path): string
    {
        if (! is_int($alias)) {
            return (string) $alias;
        }

        if (! str_contains($path, '.')) {
            return $this->belongsToPropertyConfig($path)['alias'];
        }

        return '_'.str_replace('.', '_', $path);
    }

    protected function localizeBelongsToPropertyPath(Builder $query, mixed $alias, string $path, ?string $locale): bool
    {
        if (! filled($path)) {
            return false;
        }

        /** @var Model $model */
        $model = $this;

        foreach (explode('.', $path) as $part) {
            if (! method_exists($model, $part)) {
                return false;
            }

            $relation = $model->{$part}();
            if (! $relation instanceof BelongsTo) {
                return false;
            }

            $model = $relation->getRelated();
        }

        $resolvedAlias = $this->resolveBelongsToPropertyPathAlias($alias, $path);

        if (! $this->ensureBelongsToPathJoined($query, $path, $resolvedAlias)) {
            return false;
        }

        if (! $model instanceof Localization) {
            return true;
        }

        $model->scopeAddLocalizationJoin(
            $query,
            model: $model,
            localizationAlias: $resolvedAlias,
            tableAlias: $resolvedAlias,
            locale: $locale,
        );

        return true;
    }

    /**
     * Optional model-level map.
     *
     * Example:
     * public const BELONGS_TO_PROPERTIES = [
     *     'color' => ['alias' => '_color', 'column' => 'name'],
     *     'brand' => ['alias' => '_brand', 'column' => 'title'],
     * ];
     *
     * When omitted, aliases default to "_{relation}" and columns default to "name".
     *
     * Backward compatibility: LOCALIZED_BELONGS_TO is also supported.
     *
     * @return array<string, string|array{alias?: string, column?: string}>
     */
    protected function belongsToPropertyMap(): array
    {
        $constantName = static::class.'::BELONGS_TO_PROPERTIES';
        $legacyConstantName = static::class.'::LOCALIZED_BELONGS_TO';

        if (! defined($constantName) && ! defined($legacyConstantName)) {
            return [];
        }

        $map = defined($constantName)
            ? constant($constantName)
            : constant($legacyConstantName);

        if (! is_array($map)) {
            throw new InvalidArgumentException('BELONGS_TO_PROPERTIES (or legacy LOCALIZED_BELONGS_TO) must be defined as an array.');
        }

        return $map;
    }

    /**
     * @return array{alias: string, column: string}
     */
    protected function belongsToPropertyConfig(string $relationName): array
    {
        $map = $this->belongsToPropertyMap();
        $entry = $map[$relationName] ?? null;

        if (is_string($entry)) {
            return [
                'alias' => $entry,
                'column' => 'name',
            ];
        }

        if (is_array($entry)) {
            return [
                'alias' => (string) ($entry['alias'] ?? '_'.Str::snake($relationName)),
                'column' => (string) ($entry['column'] ?? 'name'),
            ];
        }

        return [
            'alias' => '_'.Str::snake($relationName),
            'column' => 'name',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function configuredBelongsToProperties(): array
    {
        return array_keys($this->belongsToPropertyMap());
    }

    protected function belongsToPropertyRelation(string $relationName): BelongsTo
    {
        if (! method_exists($this, $relationName)) {
            throw new InvalidArgumentException("The {$relationName} relation does not exist on ".static::class.'.');
        }

        $relation = $this->{$relationName}();

        if (! $relation instanceof BelongsTo) {
            throw new InvalidArgumentException("The {$relationName} relation must be a BelongsTo relationship.");
        }

        return $relation;
    }

    protected function joinBelongsToPropertyRelation(Builder $query, string $relationName, string $alias): Builder
    {
        $joinedAliases = JoinHelper::getJoinedAliases($query);
        if (in_array($alias, $joinedAliases, true)) {
            return $query;
        }

        $relation = $this->belongsToPropertyRelation($relationName);
        $related = $relation->getRelated();

        $query->leftJoin(
            $related->getTable()." as {$alias}",
            $relation->getQualifiedForeignKeyName(),
            '=',
            "{$alias}.".$relation->getOwnerKeyName(),
        );

        return $query;
    }

    protected function addBelongsToPropertyRelationSelect(Builder $query, string $alias, string $column = 'name', SelectMode $selectMode = SelectMode::SetIfEmpty): Builder
    {
        return SelectHelper::addColumnSelect($query, $alias, $column, $selectMode);
    }

    protected function joinBelongsToPropertyLocalization(Builder $query, string $relationName, string $alias, ?string $locale = null): Builder
    {
        // Ensure the base relation join exists before referencing the alias in the localization join.
        $this->joinBelongsToPropertyRelation($query, $relationName, $alias);

        $relation = $this->belongsToPropertyRelation($relationName);
        $related = $relation->getRelated();

        if (! $related instanceof Localization) {
            return $query;
        }

        $related->scopeAddLocalizationJoin(
            $query,
            localizationAlias: $alias,
            locale: $locale,
        );

        return $query;
    }

    protected function addBelongsToPropertyLocalizationSelectForRelation(Builder $query, string $relationName, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder
    {
        $relation = $this->belongsToPropertyRelation($relationName);
        $related = $relation->getRelated();

        if (! $related instanceof Localization) {
            return $query;
        }

        // Target only this relation's localization alias directly rather than doing a full scan,
        // so multiple per-relation calls don't produce duplicate columns.
        $config = $this->belongsToPropertyConfig($relationName);

        return SelectHelper::addTranslationSelect($query, $config['alias'].'_localizables', $config['alias'], selectMode: $selectMode);
    }

    protected function addBelongsToPropertyLocalizationCoalesceForRelation(Builder $query, string $relationName, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder
    {
        $relation = $this->belongsToPropertyRelation($relationName);
        $related = $relation->getRelated();

        if (! $related instanceof Localization) {
            return $query;
        }

        // Target only this relation's alias directly — avoids cross-contaminating other joined sources.
        $config = $this->belongsToPropertyConfig($relationName);

        return SelectHelper::addCoalesceSelect($query, $config['alias'].'_localizables', $config['alias'], $config['column'], selectMode: $selectMode);
    }

    /**
     * Join a belongs-to property using BELONGS_TO_PROPERTIES config (or legacy LOCALIZED_BELONGS_TO)
     * and convention defaults.
     */
    public function scopeWithBelongsToProperty(Builder $query, string $relationName): Builder
    {
        $config = $this->belongsToPropertyConfig($relationName);

        return $this->joinBelongsToPropertyRelation($query, $relationName, $config['alias']);
    }

    /**
     * Add aliased base select for a belongs-to property.
     */
    public function scopeAddBelongsToPropertySelect(Builder $query, string $relationName, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder
    {
        $config = $this->belongsToPropertyConfig($relationName);

        return $this->addBelongsToPropertyRelationSelect(
            $query,
            $config['alias'],
            $config['column'],
            $selectMode,
        );
    }

    /**
     * Join localization for a belongs-to property when its related model is localizable.
     */
    public function scopeWithBelongsToPropertyLocalization(Builder $query, string $relationName, ?string $locale = null): Builder
    {
        $config = $this->belongsToPropertyConfig($relationName);

        return $this->joinBelongsToPropertyLocalization($query, $relationName, $config['alias'], $locale);
    }

    /**
     * Add translated select columns for a belongs-to property localization join.
     */
    public function scopeAddBelongsToPropertyLocalizationSelect(Builder $query, string $relationName, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder
    {
        return $this->addBelongsToPropertyLocalizationSelectForRelation($query, $relationName, $selectMode);
    }

    /**
     * Add COALESCE labels for a belongs-to property localization join.
     */
    public function scopeAddBelongsToPropertyLocalizationCoalesce(Builder $query, string $relationName, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder
    {
        return $this->addBelongsToPropertyLocalizationCoalesceForRelation($query, $relationName, $selectMode);
    }

    /**
     * Add raw (non-localized) selects for all configured belongs-to properties.
     */
    public function scopeAddConfiguredBelongsToPropertySelect(Builder $query, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder
    {
        $first = true;

        foreach ($this->configuredBelongsToProperties() as $relationName) {
            $query->addBelongsToPropertySelect($relationName, $first ? $selectMode : SelectMode::Ignore);
            $first = false;
        }

        return $query;
    }

    /**
     * Compatibility scope for withLocalization-like path input on belongs-to properties.
     *
     * Supported formats:
     * - 'color'
     * - ['color', 'plant', 'plant.species']
     * - ['_name_color' => 'color']
     * - ['_name_color' => ['relation' => 'color', 'locale' => 'en']]
     *
     * Every path segment must be a BelongsTo relation. Invalid paths throw InvalidArgumentException.
     * Non-localizable terminal models are joined but localization join is skipped.
     */
    public function scopeWithPropertyLocalization(Builder $query, string|array|null $paths = null, ?string $locale = null): Builder
    {
        if ($paths === null) {
            $this->applyBelongsToPropertyJoins($query, $locale);

            return $query;
        }

        if (is_string($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $alias => $value) {
            if (is_string($value)) {
                if (! $this->localizeBelongsToPropertyPath($query, $alias, $value, $locale)) {
                    throw new InvalidArgumentException('Invalid localization property path: '.$value);
                }

                continue;
            }

            if (is_array($value)) {
                if (is_string(array_key_first($value))) {
                    $relation = (string) ($value['relation'] ?? '');
                    $entryLocale = isset($value['locale']) && is_string($value['locale'])
                        ? $value['locale']
                        : $locale;
                } else {
                    $relation = isset($value[0]) && is_string($value[0]) ? $value[0] : '';
                    $entryLocale = isset($value[1]) && is_string($value[1])
                        ? $value[1]
                        : $locale;
                }

                if (! $this->localizeBelongsToPropertyPath($query, $alias, $relation, $entryLocale)) {
                    throw new InvalidArgumentException('Invalid localization property path: '.$relation);
                }

                continue;
            }

            throw new InvalidArgumentException('Invalid localization property path input.');
        }

        return $query;
    }

    /**
     * Internal method called by HasLocalizedQuery::withLocalizedData.
     * Joins every configured BelongsTo property and its localization table in one pass.
     * Safe to call multiple times — joins are idempotent.
     */
    protected function applyBelongsToPropertyJoins(Builder $query, ?string $locale = null): void
    {
        foreach ($this->configuredBelongsToProperties() as $rel) {
            $config = $this->belongsToPropertyConfig($rel);
            $this->joinBelongsToPropertyRelation($query, $rel, $config['alias']);
            $this->joinBelongsToPropertyLocalization($query, $rel, $config['alias'], $locale);
        }
    }
}
