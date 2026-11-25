<?php

namespace Database\Seeders\Concerns;

use App\Models\Contracts\Localization;
use App\Models\Language;
use Illuminate\Database\Eloquent\Model;

trait SeedsNameTranslations
{
    protected function seedNameTranslations(Model&Localization $model, array $translations): void
    {
        $existing = $model->localizations()
            ->where(fn ($query) => $query->where('field', 'name')->orWhereNull('field'))
            ->pluck('sort', 'language_id');

        if ($existing->isEmpty()) {
            $model->replaceNameTranslations($translations);

            return;
        }

        $missingLanguages = Language::query()
            ->whereIn('code', array_keys($translations))
            ->whereNotIn('id', $existing->keys())
            ->pluck('id', 'code');
        $sort = $existing->max();

        foreach ($translations as $locale => $name) {
            if ($missingLanguages->has($locale)) {
                $model->localizations()->firstOrCreate(
                    ['language_id' => $missingLanguages[$locale], 'field' => 'name'],
                    ['value' => $name, 'sort' => ++$sort],
                );
            }
        }

        $model->unsetRelation('localizations');
    }
}
