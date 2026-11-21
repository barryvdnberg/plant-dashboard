<?php

namespace App\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * This trait provides a method to ensure that a base select statement is present in the query builder. 
 * This is useful for cases where additional joins or selects are added to a query without first defining a base select, 
 * which can lead to unexpected results or errors.
 */
trait EnsuresBaseSelect
{
    /**
     * Ensure that the query has a base select statement. This is necessary for queries that add joins or selects
     * without first defining a base select, as it can lead to unexpected results or errors.
     *
     * @param Builder $query The query builder instance to check and modify if necessary.
     * @param bool $condition Optional condition to determine whether to ensure the base select. If false, the method will not modify the query. Default is true.
     * @return Builder The modified query builder instance with a base select statement if it was missing.
     */
    protected function ensureBaseSelect(Builder $query, bool $condition = true): Builder
    {
        if ($condition && empty($query->getQuery()->columns)) {
            $query->select($query->getModel()->getTable() . '.*');
        }

        return $query;
    }
}