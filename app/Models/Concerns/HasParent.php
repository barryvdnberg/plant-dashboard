<?php

namespace App\Models\Concerns;

use App\Models\Contracts\Localization;
use App\Models\Contracts\ParentRelationship;
use App\Support\Query\Enums\SelectMode;
use App\Support\Query\JoinHelper;
use App\Support\Query\SelectHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use InvalidArgumentException;

/**
 * @mixin Model
 *
 * @phpstan-require-extends Model
 *
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait HasParent
{
    protected static string $parentAlias = '_parent';

    protected static string $parentNameColumn = 'name';

    protected function getParentAlias(): string
    {
        return static::$parentAlias;
    }

    protected function getParentNameColumn(): string
    {
        return static::$parentNameColumn;
    }

    /**
     * Adds left join clauses to the given query builder to join the parent tables to the child table based on the defined parent relationships in the model.
     *
     * This method iteratively adds left join clauses to the given query builder for each level of parent relationship defined in the model, up to the specified depth.
     * Repeated calls reuse existing parent joins.
     *
     * @return Builder The modified query builder instance with the added join clauses for the parent relationships.
     */
    public function scopeWithParent(Builder $query, int $depth = 1): Builder
    {
        /** @var Model $model */
        $model = $this;
        $childAlias = $this->getTable();
        for ($i = 0; $i < $depth && $model instanceof ParentRelationship; $i++) {
            $relation = $model->parent();
            $model = $relation->getRelated();
            $alias = str_repeat($this->getParentAlias(), $i + 1);

            if (! in_array($alias, JoinHelper::getJoinedAliases($query), true)) {
                $this->addParentJoinClause($query, $model->getTable(), $alias,
                    $relation->getOwnerKeyName(), $childAlias, $relation->getForeignKeyName());
            }
            $childAlias = $alias;
        }

        return $query;
    }

    /**
     * Adds select clauses to the given query builder to select the name column from the parent tables in the query.
     *
     * This method retrieves the aliases of the joined parent tables from the given query builder, and then adds select clauses to select the name column from each of the parent tables using their respective aliases. The selected name columns are aliased as "{parentAlias}_name" in the resulting query.
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  SelectMode  $selectMode  Selected behaviour for changing the select columns.
     * @return Builder The modified query builder instance with the added select clauses for the parent name columns.
     */
    public function scopeAddParentSelect(Builder $query, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder
    {
        return SelectHelper::addColumnSelectForSuffix(
            $query,
            $this->getParentAlias(),
            $this->getParentNameColumn(),
            $selectMode,
        );
    }

    /**
     * Adds a left join clause to the given query builder to join the parent table to the child table based on the defined parent relationship in the model.
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  Model|null  $parentModel  The parent model to join. If null, the method will attempt to determine the parent model by traversing the parent relationships defined up to this point in the query.
     * @return Builder The modified query builder instance with the added join clause for the parent relationship
     */
    public function scopeAddParentJoin(Builder $query, ?Model $parentModel = null): Builder
    {
        /** @var Model $self */
        $self = $this;

        $parentAlias = $this->getParentAlias();
        $joins = JoinHelper::getJoinedAliases($query);

        $highestcount = 0;
        $childAlias = '';

        foreach ($joins as $join) {
            if (! preg_match('/^(?:'.preg_quote($parentAlias, '/').')+$/', $join)) {
                continue;
            }
            $parentDepth = substr_count($join, $parentAlias);
            if ($parentDepth > $highestcount) {
                $highestcount = $parentDepth;
                $childAlias = $join;
            }
        }

        $parentAlias = $childAlias.$this->getParentAlias();

        if ($childAlias === '') {
            $childAlias = $self->getTable();
        }

        $child = $this;
        for ($i = 0; $i < $highestcount; $i++) {
            $this->implementsParentRelationship($child);
            $child = $child->parent()->getRelated();
        }
        $this->implementsParentRelationship($child);
        $relation = $child->parent();
        $parentModel ??= $relation->getRelated();

        $parentTable = $parentModel->getTable();

        return $this->addParentJoinClause(
            $query,
            $parentTable,
            $parentAlias,
            $relation->getOwnerKeyName(),
            $childAlias,
            $relation->getForeignKeyName()
        );
    }

    /**
     * Adds a left join clause to the given query builder to join the parent table to the child table.
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  string  $parentTable  The name of the parent table.
     * @param  string  $parentAlias  The alias to use for the parent table in the join.
     * @param  string  $parentKeyField  The primary key field of the parent table.
     * @param  string  $childTable  The name of the child table.
     * @param  string  $childForeignKeyField  The foreign key field in the child table that references the parent table.
     * @return Builder The modified query builder instance with the added join clause.
     */
    protected function addParentJoinClause(Builder $query, string $parentTable, string $parentAlias, string $parentKeyField, string $childTable, string $childForeignKeyField): Builder
    {
        return $query->leftJoin("$parentTable as $parentAlias", function (JoinClause $join) use ($parentAlias, $parentKeyField, $childTable, $childForeignKeyField) {
            $join->on("$parentAlias.$parentKeyField", '=', "$childTable.$childForeignKeyField");
        });
    }

    protected function implementsParentRelationship(Model $model, bool $throwException = true): bool
    {
        if (! $model instanceof ParentRelationship) {
            if ($throwException) {
                throw new InvalidArgumentException('Model '.get_class($model).' does not have a parent relationship defined.');
            }

            return false;
        }

        return true;
    }

    /**
     * Joins the parent tables and their localization tables for the requested depth in a single call.
     * This is the primary parent join scope — use this instead of calling withParent and the old
     * withParentLocalization separately.
     *
     * @param  Builder  $query  The query builder instance to modify.
     * @param  int  $depth  How many parent generations to join and localise.
     * @param  string|null  $locale  Locale to filter localizations by. Defaults to the application locale.
     */
    public function scopeWithLocalizedParent(Builder $query, int $depth = 1, ?string $locale = null): Builder
    {
        $this->scopeWithParent($query, $depth);

        $model = $this;
        $parentAlias = $this->getParentAlias();

        for ($i = 0; $i < $depth; $i++) {
            if (! $model instanceof ParentRelationship) {
                break;
            }

            $parentModel = $model->parent()->getModel();

            if ($parentModel instanceof Localization) {
                // Alias stacks to match how scopeAddParentJoin builds aliases:
                // depth 1 → '_parent', depth 2 → '_parent_parent', etc.
                $parentModel->scopeAddLocalizationJoin(
                    $query,
                    localizationAlias: str_repeat($parentAlias, $i + 1),
                    locale: $locale,
                );
            }

            $model = $parentModel;
        }

        return $query;
    }
}
