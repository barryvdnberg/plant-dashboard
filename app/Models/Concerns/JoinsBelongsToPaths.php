<?php

namespace App\Models\Concerns;

use App\Support\Query\JoinHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait JoinsBelongsToPaths
{
    protected function ensureBelongsToPathJoined(Builder $query, string $path, string $finalAlias): bool
    {
        if ($path === '') {
            return true;
        }

        /** @var Model $currentModel */
        $currentModel = $this;
        $currentAlias = $query->getModel()->getTable();
        $joinedAliases = JoinHelper::getJoinedAliases($query);
        $parts = explode('.', $path);
        $lastIndex = count($parts) - 1;
        $aliasPrefix = [];

        foreach ($parts as $index => $part) {
            if (! method_exists($currentModel, $part)) {
                return false;
            }

            $relation = $currentModel->{$part}();
            if (! $relation instanceof BelongsTo) {
                return false;
            }

            $related = $relation->getRelated();

            if ($index === $lastIndex) {
                $nextAlias = $finalAlias;
            } else {
                $aliasPrefix[] = $part;
                $nextAlias = '_'.implode('_', $aliasPrefix);
            }

            if (! in_array($nextAlias, $joinedAliases, true)) {
                $query->leftJoin(
                    $related->getTable()." as {$nextAlias}",
                    $currentAlias.'.'.$relation->getForeignKeyName(),
                    '=',
                    $nextAlias.'.'.$relation->getOwnerKeyName(),
                );

                $joinedAliases[] = $nextAlias;
            }

            $currentModel = $related;
            $currentAlias = $nextAlias;
        }

        return true;
    }
}
