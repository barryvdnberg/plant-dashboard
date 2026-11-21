<?php

namespace App\Support\Query;

use App\Support\Query\Enums\SelectMode;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SelectHelper
{
    
    /**
     * Ensure that the query has a base select statement.
     */
    public static function ensureBaseSelect(Builder $query, SelectMode $mode): Builder
    {
        if ($mode == SelectMode::Ignore) {
            return $query;
        }
        
        else if ($mode == SelectMode::SetIfEmpty) {
            if (empty($query->getQuery()->columns)) {
                $query->select($query->getModel()->getTable() . '.*');
            }
        }

        else if ($mode == SelectMode::OnlyKeepId) {
            $query->select($query->getModel()->getTable() . '.' . $query->getModel()->getKeyName());
        }

        return $query;
    }

    /**
     * Add a single `{alias}.{column} as {alias}_{column}` select column.
     */
    public static function addColumnSelect(
        Builder $query,
        string $alias,
        string $column,
        SelectMode $selectMode = SelectMode::SetIfEmpty,
    ): Builder {
        self::ensureBaseSelect($query, $selectMode);

        return $query->addSelect("{$alias}.{$column} as {$alias}_{$column}");
    }

    /**
     * Scan all joined aliases ending with $suffix and add a `{join}.{column} as {join}_{column}` column for each.
     * Used by HasParent to add the parent name column for every joined parent level.
     */
    public static function addColumnSelectForSuffix(
        Builder $query,
        string $suffix,
        string $column,
        SelectMode $selectMode = SelectMode::SetIfEmpty,
    ): Builder {
        self::ensureBaseSelect($query, $selectMode);

        $joins = JoinHelper::getJoinedAliases($query);
        $columns = [];

        foreach ($joins as $join) {
            if (str_ends_with($join, $suffix)) {
                $columns[] = "{$join}.{$column} as {$join}_{$column}";
            }
        }

        if ($columns === []) {
            return $query;
        }

        return $query->addSelect($columns);
    }

    /**
     * Add a single `{locAlias}.{locColumn} as {alias}_translation` select column.
     */
    public static function addTranslationSelect(
        Builder $query,
        string $locAlias,
        string $alias,
        string $locColumn = 'value',
        SelectMode $selectMode = SelectMode::SetIfEmpty,
    ): Builder {
        self::ensureBaseSelect($query, $selectMode);

        return $query->addSelect("{$locAlias}.{$locColumn} as {$alias}_translation");
    }

    /**
     * Add a single `COALESCE({locAlias}.{locColumn}, {alias}.{fallbackColumn}) as {alias}_label` select column.
     */
    public static function addCoalesceSelect(
        Builder $query,
        string $locAlias,
        string $alias,
        string $fallbackColumn = 'name',
        string $locColumn = 'value',
        SelectMode $selectMode = SelectMode::SetIfEmpty,
    ): Builder {
        self::ensureBaseSelect($query, $selectMode);

        return $query->addSelect(DB::raw("COALESCE({$locAlias}.{$locColumn}, {$alias}.{$fallbackColumn}) as {$alias}_label"));
    }

    /**
     * Scan all joined aliases ending with $localizationSuffix and add a
     * `{prefix}_translation` column for each (the raw nullable translation value).
     */
    public static function addLocalizationSelectColumns(
        Builder $query,
        string $localizationSuffix,
        string $localizationColumn,
        SelectMode $selectMode = SelectMode::SetIfEmpty,
    ): Builder {
        self::ensureBaseSelect($query, $selectMode);

        $joins = JoinHelper::getJoinedAliases($query);
        $columns = [];

        foreach ($joins as $join) {
            if (str_ends_with($join, $localizationSuffix)) {
                $prefix = Str::beforeLast($join, $localizationSuffix);
                $columns[] = "{$join}.{$localizationColumn} as {$prefix}_translation";
            }
        }

        if ($columns === []) {
            return $query;
        }

        return $query->addSelect($columns);
    }

    /**
     * Scan all joined aliases ending with $localizationSuffix and add a
     * `COALESCE(loc.value, table.col) as {prefix}_label` column for each.
     * The fallback table/column is derived from each join's ON clause.
     */
    public static function addLocalizationCoalesceColumns(
        Builder $query,
        string $localizationSuffix,
        string $localizationColumn,
        SelectMode $selectMode = SelectMode::SetIfEmpty,
    ): Builder {
        self::ensureBaseSelect($query, $selectMode);

        $joinClauses = JoinHelper::getJoinedStatements($query);
        $columns = [];

        foreach ($joinClauses as $joinClause) {
            $table = $joinClause->table;
            $parts = explode(' as ', $table);

            if (count($parts) !== 2) {
                continue;
            }

            $primaryName = $parts[1];

            if (!str_ends_with($primaryName, $localizationSuffix)) {
                continue;
            }

            $on = collect($joinClause->wheres)->firstWhere('type', 'Column');

            if (!$on) {
                continue;
            }

            $secondName = explode('.', (string) $on['second'])[0] ?? null;

            if (!$secondName) {
                continue;
            }

            $prefix = Str::beforeLast($primaryName, $localizationSuffix);
            $columns[] = DB::raw("COALESCE({$primaryName}.{$localizationColumn}, {$secondName}.name) as {$prefix}_label");
        }

        if ($columns === []) {
            return $query;
        }

        return $query->addSelect($columns);
    }
}