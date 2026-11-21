<?php

namespace App\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

/**
 * Trait InteractsWithJoins
 * 
 * Provides utility methods for working with joined tables in Eloquent queries, such as retrieving the aliases of joined tables.
 * This is particularly useful for models that need to dynamically determine which tables have been joined in a
 * query, such as when applying localization or parent relationships.
 */
trait InteractsWithJoins
{
    /**
     * Get the aliases of joined tables in the given query builder.
     * 
     * This method inspects the "joins" property of the underlying query and extracts the aliases of any joined tables.
     * It handles both string table names and Expression instances, and looks for "AS alias" patterns to determine the alias.
     * 
     * @param Builder $query The Eloquent query builder to inspect.
     * @return list<string> A list of string aliases for the joined tables.
     */
    protected function getJoinedAliases(Builder $query): array
    {
        $tables = $this->getJoinedTables($query);
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
     * This method inspects the "joins" property of the underlying query and extracts the table names of any joined tables.
     * 
     * @param Builder $query The Eloquent query builder to inspect.
     * @return list<string> A list of string table names for the joined tables.
     */
    protected function getJoinedTables(Builder $query): array
    {
        $joins = $this->getJoinedStatements($query);
        // $join1 = $joins[1];

        // dd($join1, $join1->table, $join1->wheres);
        $tables = [];

        foreach ($joins as $join) {
            $table = $join->table ?? null;

            // Expression or string normalize into a string
            if ($table instanceof Expression) {
                $table = (string) $table;
            }

            if (!is_string($table)) {
                continue;
            }

            // not sure if this is necessary, but trim whitespace just in case
            $table = trim($table);

            $tables[] = $table;
        }
        // dd($tables);

        return $tables;
    }


    /**
     * Get the join statements from the underlying query builder.
     * 
     * This method accesses the "joins" property of the underlying query builder, which contains the join statements for the query.
     * 
     * @param Builder $query The Eloquent query builder to inspect.
     * @return list<\Illuminate\Database\Query\JoinClause> A list of join statements for the query.
     */
    protected function getJoinedStatements(Builder $query): array
    {
        return $query->getQuery()->joins ?? [];
    }
}