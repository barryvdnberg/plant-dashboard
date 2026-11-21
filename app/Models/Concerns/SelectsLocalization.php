<?php

namespace App\Models\Concerns;

use App\Support\Query\Enums\SelectMode;
use App\Support\Query\SelectHelper;
use Illuminate\Database\Eloquent\Builder;

trait SelectsLocalization
{
    protected static string $localizationAlias = '_localizables';

    protected static string $localizationColumn = 'value';

    protected function getLocalizationAlias(): string
    {
        return static::$localizationAlias;
    }

    protected function getLocalizationColumn(): string
    {
        return static::$localizationColumn;
    }

    /**
     * Adds select statements for any joined localizations to get the localized value.
     * Scans all joined aliases ending with the localization suffix and adds a
     * `{prefix}_translation` column for each.
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  SelectMode  $selectMode  Selected behaviour for changing the select columns.
     * @return Builder The modified query builder instance with the added localization selects.
     */
    public function scopeAddLocalizationSelect(Builder $query, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder
    {
        return SelectHelper::addLocalizationSelectColumns(
            $query,
            $this->getLocalizationAlias(),
            $this->getLocalizationColumn(),
            $selectMode,
        );
    }

    /**
     * Adds COALESCE selects for any joined localizations, falling back to the original name column
     * when no translation exists for the current locale. Scans all joined aliases ending with the
     * localization suffix and adds a `COALESCE(loc.value, table.name) as {prefix}_label` for each.
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  SelectMode  $selectMode  Selected behaviour for changing the select columns.
     * @return Builder The modified query builder instance with the added COALESCE selects.
     */
    public function scopeAddLocalizationCoalesce(Builder $query, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder
    {
        return SelectHelper::addLocalizationCoalesceColumns(
            $query,
            $this->getLocalizationAlias(),
            $this->getLocalizationColumn(),
            $selectMode,
        );
    }
}
