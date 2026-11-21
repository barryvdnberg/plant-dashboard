<?php

namespace App\Models\Contracts;

use App\Support\Query\Enums\SelectMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface ParentRelationship
{
    public function parent(): BelongsTo;
    public function scopeWithParent(Builder $query, int $depth = 1): Builder;
    public function scopeAddParentSelect(Builder $query, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder;
    public function scopeAddParentJoin(Builder $query, ?Model $parentModel = null): Builder;
    public function scopeWithLocalizedParent(Builder $query, int $depth = 1, ?string $locale = null): Builder;
}