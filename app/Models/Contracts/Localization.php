<?php

namespace App\Models\Contracts;

use App\Support\Query\Enums\SelectMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Localization
{
    public function getTranslation(?string $locale = null, string $field = 'name'): ?string;

    public function getTranslatedName(?string $locale = null, ?int $depth = null): string;

    public function getLocalizedDisplayName(): string;

    public function replaceNameTranslations(array $translations): void;

    public function localizations(): MorphMany;

    public function scopeWithLocalization(Builder $query, string|array|null $paths, ?string $locale): Builder;

    public function scopeAddLocalizationSelect(Builder $query, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder;

    public function scopeAddLocalizationCoalesce(Builder $query, SelectMode $selectMode = SelectMode::SetIfEmpty): Builder;

    public function scopeAddLocalizationJoin(Builder $query, ?Model $model = null, ?string $localizationAlias = null, ?string $tableAlias = null, ?string $locale = null): Builder;
}
