<?php

namespace App\Models\Concerns;

use App\Models\Language;
use App\Models\Localizable;
use App\Support\Languages;
use App\Support\Query\JoinHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Str;

/**
 * @mixin Model
 *
 * @phpstan-require-extends Model
 *
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait HasLocalization
{
    use HasLocalizedName;
    use JoinsBelongsToPaths;
    use SelectsLocalization;

    /** Ensure localizations are deleted when the model is deleted */
    public static function bootHasLocalization(): void
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::class;

        $modelClass::deleting(function (Model $model): void {
            $model->localizations()->delete();
        });
    }

    public function localizations(): MorphMany
    {
        /** @var Model $model */
        $model = $this;

        return $model->morphMany(Localizable::class, 'localizable');
    }

    /** Replace seeded name translations while preserving translations of other fields. */
    public function replaceNameTranslations(array $translations): void
    {
        $languages = Language::query()->pluck('id', 'code');
        $rows = [];

        foreach ($translations as $code => $value) {
            if (! isset($languages[$code]) || blank(trim((string) $value))) {
                continue;
            }
            $rows[] = ['language_id' => $languages[$code], 'field' => 'name', 'value' => $value, 'sort' => count($rows) + 1];
        }

        if ($rows === []) {
            return;
        }

        $this->getConnection()->transaction(function () use ($rows): void {
            // Model events invalidate any cached labels for removed languages too.
            $this->localizations()
                ->where(fn ($query) => $query->where('field', 'name')->orWhereNull('field'))
                ->get()->each->delete();
            $this->localizations()->createMany($rows);
        });
        $this->unsetRelation('localizations');
    }

    protected static string $localizationTable = 'localizables';

    // auto discovers based on localizationTable
    protected static ?string $localizationForeignKeyField = null;

    protected static ?string $localizationTypeField = null;

    protected static ?string $languageIdField = null;

    protected function getLocalizationTable(): string
    {
        return static::$localizationTable;
    }

    protected function getLocalizationName(): string
    {
        return Str::singular($this->getLocalizationTable());
    }

    protected function getLocalizationForeignKeyField(): string
    {
        return static::$localizationForeignKeyField ?? $this->getLocalizationName().'_id';
    }

    protected function getLocalizationTypeField(): string
    {
        return static::$localizationTypeField ?? $this->getLocalizationName().'_type';
    }

    protected function getLanguageIdField(): string
    {
        return static::$languageIdField ?? 'language_id';
    }

    /**
     * Joins the localizations table for the model and optionally for related models
     * based on the provided paths.
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  string|array|null  $paths
     *                                    Optional single or list of relationship paths to also localize. Formats can be mixed in the same array
     *
     *     Supported formats:
     *
     *     - 'relation'
     *      e.g. withLocalization('parent')
     *     - 'alias' => 'relation'
     *      e.g. withLocalization(['_parent' => 'parent'])
     *     - 'alias' => ['relation' => ..., 'locale' => ...] (all args are optional)
     *      e.g. withLocalization(['_parent' => ['relation' => 'parent']])
     * @param  string|null  $locale  The locale to filter localizations by. If null, the default locale will be used.
     * @return Builder The modified query builder instance with the necessary joins.
     */
    public function scopeWithLocalization(Builder $query, string|array|null $paths = null, $locale = null): Builder
    {
        $query->addLocalizationJoin(locale: $locale);

        if ($paths) {
            if (is_string($paths)) {
                $paths = [$paths];
            }

            foreach ($paths as $alias => $value) {
                if ($this->localizableByStringPattern($query, $alias, $value, $locale)) {
                    continue;
                } elseif ($this->localizableByModelPattern($query, $alias, $value, $locale)) {
                    continue;
                } elseif ($this->localizableByArrayPattern($query, $alias, $value, $locale)) {
                    continue;
                } else {
                    throw new \InvalidArgumentException('Invalid localization path: '.(is_string($value) ? $value : gettype($value)));
                }
            }
        }

        return $query;
    }

    /**
     * Adds a localization join to the query for the model, with optional parameters to specify the model being
     * localized (for nested relations), the alias for the join, the table alias used to qualify the join key,
     * and the locale to filter by. This method can be used directly or through scopeWithLocalization.
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  Model|null  $model  The model being localized; determines the class used for the type condition and
     *                             the primary key name. Defaults to the current model when null.
     * @param  string|null  $localizationAlias  The alias prefix for the joined localizations table.
     *                                          If null, falls back to getLocalizationAlias().
     * @param  string|null  $tableAlias  An explicit table alias used to qualify the model's key field in the ON
     *                                   clause (e.g. "_parent" when the model is already joined under that alias).
     *                                   When null, the localizationAlias or the model's fully-qualified key is used.
     * @param  string|null  $locale  The locale to filter localizations by. Defaults to the application locale.
     * @return Builder The modified query builder instance with the added localization join.
     */
    public function scopeAddLocalizationJoin(Builder $query, ?Model $model = null, ?string $localizationAlias = null, ?string $tableAlias = null, ?string $locale = null): Builder
    {
        $joinTable = $this->getLocalizationTable();
        /** Allow overwriting the model to make sure you can do nested joins */
        $model ??= new static;
        /** @var Model $model */
        $languageId = Languages::localeId($locale);

        if ($tableAlias !== null) {
            $modelQualifiedKeyField = $tableAlias.'.'.$model->getKeyName();
        } elseif ($localizationAlias !== null) {
            $modelQualifiedKeyField = $localizationAlias.'.'.$model->getKeyName();
        } else {
            $modelQualifiedKeyField = $model->getQualifiedKeyName();
        }

        $resolvedAlias = $localizationAlias !== null
            ? $localizationAlias.$this->getLocalizationAlias()
            : $this->getLocalizationAlias();

        $this->addLocalizationJoinClause(
            $query,
            $joinTable,
            $model->getMorphClass(),
            $modelQualifiedKeyField,
            $resolvedAlias,
            $this->getLocalizationForeignKeyField(),
            $this->getLocalizationTypeField(),
            $this->getLanguageIdField(),
            $languageId
        );

        return $query;
    }

    /**
     * Adds a join clause to the query builder with the necessary conditions to join the localizations table for the model. This method is used internally by scopeWithLocalization and can also be used directly if you need more control over the join conditions or want to join the localizations table without using the scope.
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  string  $joinTable  The name of the localizations table to join.
     * @param  string  $modelName  The class name of the model being localized, used for the type condition in the join.
     * @param  string  $modelQualifiedKeyField  The fully qualified key field of the model to join on (e.g. "plants.id").
     * @param  string  $localizationAlias  The alias to use for the joined localizations table.
     * @param  string  $localizationForeignKeyField  The foreign key field in the localizations table that references the model's key.
     * @param  string  $localizationTypeField  The field in the localizations table that specifies the type of the localized model.
     * @param  string  $localizationLanguageIdField  The field in the localizations table that specifies the language ID.
     * @param  int  $languageId  The language ID to filter localizations by in the join condition.
     * @return Builder The modified query builder instance with the added join clause.
     */
    protected function addLocalizationJoinClause(Builder $query, string $joinTable, string $modelName, string $modelQualifiedKeyField, string $localizationAlias, string $localizationForeignKeyField, string $localizationTypeField, string $localizationLanguageIdField, ?int $languageId): Builder
    {
        if (in_array($localizationAlias, JoinHelper::getJoinedAliases($query), true)) {
            return $query;
        }

        // Select one name translation; other translated fields must never duplicate model rows.
        $translation = $query->getQuery()->newQuery()->from($joinTable.' as _name_translation')
            ->select('_name_translation.id')
            ->whereColumn('_name_translation.'.$localizationForeignKeyField, $modelQualifiedKeyField)
            ->where('_name_translation.'.$localizationTypeField, $modelName)
            ->where('_name_translation.'.$localizationLanguageIdField, $languageId)
            ->where(fn ($query) => $query->where('_name_translation.field', 'name')->orWhereNull('_name_translation.field'))
            ->whereRaw("TRIM(_name_translation.value) <> ''")
            ->orderByRaw("CASE WHEN _name_translation.field = 'name' THEN 0 ELSE 1 END")
            ->orderBy('_name_translation.id')
            ->limit(1);

        return $query->leftJoin("$joinTable as $localizationAlias", function (JoinClause $join) use ($translation, $localizationAlias, $localizationForeignKeyField, $modelQualifiedKeyField, $localizationTypeField, $modelName, $localizationLanguageIdField, $languageId) {
            $join
                ->on("$localizationAlias.$localizationForeignKeyField", '=', $modelQualifiedKeyField)
                ->where("$localizationAlias.$localizationTypeField", '=', $modelName)
                ->where("$localizationAlias.$localizationLanguageIdField", '=', $languageId)
                ->where("$localizationAlias.id", '=', $translation);
        });
    }

    protected function resolveLocalizationPathAlias(mixed $alias, string $path): string
    {
        if (! is_int($alias)) {
            return (string) $alias;
        }

        // For single-segment belongs-to properties configured via HasBelongsToProperties,
        // prefer the configured alias so withLocalization('color') matches withBelongsToProperty('color').
        if ($path !== '' && ! str_contains($path, '.') && method_exists($this, 'belongsToPropertyConfig')) {
            try {
                /** @var array{alias?: string} $config */
                $config = $this->belongsToPropertyConfig($path);

                if (isset($config['alias']) && is_string($config['alias']) && $config['alias'] !== '') {
                    return $config['alias'];
                }
            } catch (\Throwable) {
                // Fall back to conventional aliasing when config resolution fails.
            }
        }

        return '_'.str_replace('.', '_', $path);
    }

    /**
     * Handles the string pattern for localization paths, which is the most common and straightforward way to specify a relation to localize. This method will attempt to process the value as a dot-notated relation path and add the necessary joins for localization.
     *
     * Supported format:
     * - 'alias' => 'relation'
     * - 'relation' (alias will be auto-generated based on the relation name)
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  mixed  $alias  The alias for the localization join
     * @param  mixed  $value  the value for the localization path
     * @param  string|null  $locale  The locale to filter localizations by.
     * @return bool Returns true if the string pattern was successfully processed and the join was added
     */
    protected function localizableByStringPattern(Builder $query, mixed $alias, mixed $value, ?string $locale): bool
    {
        if (is_string($value)) {
            /** @var Model $model */
            $model = $this;

            if (filled($value)) {
                $parts = explode('.', $value);
                foreach ($parts as $part) {
                    // check if it exists first to prevent errors in case of invalid paths
                    if (! method_exists($model, $part)) {
                        return false;
                    }

                    $relation = $model->{$part}();
                    if (! $relation instanceof BelongsTo) {
                        return false;
                    }

                    $model = $relation->getRelated();
                }
            }

            $resolvedAlias = $this->resolveLocalizationPathAlias($alias, $value);
            $tableAlias = $model->getTable();

            if (filled($value)) {
                if (! $this->ensureBelongsToPathJoined($query, $value, $resolvedAlias)) {
                    return false;
                }

                $tableAlias = $resolvedAlias;
            }

            $query->addLocalizationJoin(
                model: $model,
                localizationAlias: $resolvedAlias,
                tableAlias: $tableAlias,
                locale: $locale
            );

            return true;
        }

        return false;
    }

    /**
     * Handles the model pattern for localization paths, which allows passing an actual model instance instead of a relation string. This can be useful for more complex queries where the relation path may not be straightforward or when you want to reuse a model instance across multiple localizations.
     *
     * Supported format:
     * - 'alias' => ModelInstance
     * - ModelInstance (alias will be auto-generated based on the model's table name)
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  mixed  $alias  The alias for the localization join
     * @param  mixed  $value  the value for the localization path
     * @param  string|null  $locale  The locale to filter localizations by.
     * @return bool Returns true if the model pattern was successfully processed and the join was added
     */
    protected function localizableByModelPattern(Builder $query, mixed $alias, mixed $value, ?string $locale): bool
    {
        if ($value instanceof Model) {
            if (is_int($alias)) {
                $alias = '_'.$value->getTable();
            }
            $query->addLocalizationJoin(
                model: $value,
                localizationAlias: $alias,
                locale: $locale
            );

            return true;
        }

        return false;
    }

    /**
     * Handles the array pattern for localization paths, which can specify both the relation and the locale in a single array.
     *
     * Supported formats (all args are optional):
     * - ['relation' => ..., 'locale' => ...]
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  mixed  $alias  The alias for the localization join
     * @param  mixed  $value  the value for the localization path
     * @param  string|null  $locale  The locale to filter localizations by.
     * @return bool Returns true if the array pattern was successfully processed and the join was added, false otherwise.
     */
    protected function localizableByArrayPattern(Builder $query, mixed $alias, mixed $value, ?string $locale): bool
    {
        if (is_array($value)) {
            if (count($value) == 0) {
                return false;
            }

            // check if $value has 'relations' key or if no keys are strings where it should be the first element
            // order of elements if int array => ['relation', 'locale']
            if (is_string(array_key_first($value))) {
                $relation = $value['relation'] ?? '';
                $locale = $value['locale'] ?? $locale;
            } else {
                $relation = $value[0];
                if (isset($value[1]) && is_string($value[1])) {
                    $locale = $value[1];
                }
            }

            if ($this->localizableByStringPattern($query, $alias, $relation, $locale)) {
                return true;
            }

            return false;
        }

        return false;
    }
}
