<?php

namespace App\Support\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;

class JoinHelper
{
    /**
     * Get the aliases of joined tables in the given query builder.
     *
     * @param Builder $query
     * @return array<int, string>
     */
    public static function getJoinedAliases(Builder $query): array
    {
        $tables = self::getJoinedTables($query);
        $aliases = [];

        foreach ($tables as $table) {
            if (preg_match('/\bas\b\s+(.+)$/i', $table, $m)) {
                $alias = trim($m[1]);
                $alias = trim($alias, "`\"'");

                $aliases[] = $alias;
            }
        }

        return $aliases;
    }

    /**
     * Get the table names of joined tables in the given query builder.
     *
     * @param Builder $query
     * @return array<int, string>
     */
    public static function getJoinedTables(Builder $query): array
    {
        $joins = self::getJoinedStatements($query);
        $tables = [];

        foreach ($joins as $join) {
            $table = $join->table ?? null;

            if ($table instanceof Expression) {
                $table = (string) $table;
            }

            if (! is_string($table)) {
                continue;
            }

            $tables[] = trim($table);
        }

        return $tables;
    }

    /**
     * Get the join clauses from the underlying query builder.
     *
     * @param Builder $query
     * @return array<int, JoinClause>
     */
    public static function getJoinedStatements(Builder $query): array
    {
        return $query->getQuery()->joins ?? [];
    }
}