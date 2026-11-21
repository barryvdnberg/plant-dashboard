<?php

namespace App\Models\Concerns;

use App\Enums\NameDisplay;
use App\Models\Contracts\Localization;
use App\Models\Contracts\ParentRelationship;
use App\Support\Languages;
use App\Support\NameDisplayPreferences;

/** Shared name resolution for both loaded models and panel labels. */
trait HasLocalizedName
{
    public function getTranslation(?string $locale = null, string $field = 'name'): ?string
    {
        $languageId = Languages::localeId($locale);
        if ($languageId === null) {
            return null;
        }
        $translations = $this->relationLoaded('localizations')
            ? $this->localizations
            : $this->localizations()->where('language_id', $languageId)->get();

        // Older name translations have a null field. Explicit fields take precedence.
        foreach ([$field, ...($field === 'name' ? [null] : [])] as $candidate) {
            $translation = $translations->first(fn ($translation) => (int) $translation->language_id === $languageId
                && $translation->field === $candidate
                && filled(trim((string) $translation->value))
            );

            if ($translation) {
                return $translation->value;
            }
        }

        return null;
    }

    protected function localizedNameDepth(): int
    {
        return 0;
    }

    protected function missingParentName(): string
    {
        return '';
    }

    /** Translate each requested generation independently, falling back to its stored name. */
    public function getTranslatedName(?string $locale = null, ?int $depth = null): string
    {
        $name = $this->getTranslation($locale) ?? $this->name ?? '';

        return $this->nameWithLocalizedParents($name, $locale, $depth);
    }

    protected function nameWithLocalizedParents(string $name, ?string $locale = null, ?int $depth = null): string
    {
        $depth ??= $this->localizedNameDepth();

        if ($depth > 0 && $this instanceof ParentRelationship) {
            $parent = $this->parent;
            $parentName = $parent instanceof Localization
                ? $parent->getTranslatedName($locale, $depth - 1)
                : ($parent->name ?? $this->missingParentName());

            return trim($parentName.' '.$name);
        }

        return $name;
    }

    public function getTranslatedNameAttribute(): string
    {
        return $this->getTranslatedName();
    }

    public function getDisplayNameAttribute(): string
    {
        if (app(NameDisplayPreferences::class)->forModel($this) === NameDisplay::Scientific) {
            return $this->scientific_name;
        }

        return $this->getLocalizedDisplayName();
    }

    public function getLocalizedDisplayName(): string
    {
        return $this->getTranslation() ?? $this->nameWithLocalizedParents($this->name ?? '');
    }
}
