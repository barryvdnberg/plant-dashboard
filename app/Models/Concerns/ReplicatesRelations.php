<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait ReplicatesRelations
{
    public array $replicateable_relations = [];

    public function replicateable_relations(array $relations)
    {
        $this->replicateable_relations = $relations;
    }

    public function replicateRelationsTo(Model $copy)
    {
        if (defined(static::class.'::REPLICATEABLE_RELATIONS')) {
            $this->replicateRelations($copy, static::REPLICATEABLE_RELATIONS);
        }
    }

    protected function replicateRelations(Model $copy, array $relations): void
    {
        foreach ($relations as $relation) {
            $this->replicateRelation($copy, $relation);
        }
    }

    protected function replicateRelation(Model $copy, string $relation): void
    {
        $relationType = $this->$relation();
        match (true) {
            $relationType instanceof HasOne => $this->replicateHasOne($copy, $relation),
            $relationType instanceof BelongsToMany,
            $relationType instanceof MorphToMany => $this->replicateBelongsToMany($copy, $relation),
            $relationType instanceof HasMany,
            $relationType instanceof MorphMany => $this->replicateHasMany($copy, $relation),
        };
    }

    protected function replicateHasOne(Model $copy, string $relation): void
    {
        $related = $this->$relation;

        if (! $related) {
            return;
        }

        $new = $copy->$relation()->save($related->replicate());

        if (method_exists($related, 'replicateRelationsTo')) {
            $related->replicateRelationsTo($new);
        }
    }

    protected function replicateHasMany(Model $copy, string $relation): void
    {
        $related = $this->$relation;

        if (! $related) {
            return;
        }

        $collection = $related->map(fn ($item) => $item->replicate());

        $copy->$relation()->saveMany($collection);
        foreach ($related as $index => $item) {
            if (method_exists($item, 'replicateRelationsTo')) {
                $item->replicateRelationsTo($collection[$index]);
            }
        }
    }

    protected function replicateBelongsToMany(Model $copy, string $relation): void
    {
        $related = $this->$relation;

        if (empty($related)) {
            return;
        }

        foreach ($related as $item) {

            $pivotColumns = collect($item->pivot->getAttributes())
                ->reject(function ($value, $key) {
                    return str_ends_with($key, '_id') || str_ends_with($key, '_type');
                })
                ->all();

            $attributes = [];
            foreach ($pivotColumns as $column => $value) {
                $attributes[$column] = $value;
            }

            $copy->$relation()->attach($item->id, $attributes);
        }
    }
}
