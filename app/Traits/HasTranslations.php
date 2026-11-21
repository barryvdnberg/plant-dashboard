<?php

namespace App\Traits;

use App\Models\Contracts\Localization;
use Illuminate\Database\Eloquent\Model;

/** Adapter for seed catalogs indexed by the model's stored name. */
trait HasTranslations
{
    public function applyTranslations(Model&Localization $model, array $translations): void
    {
        if (isset($translations[$model->name])) {
            $model->replaceNameTranslations($translations[$model->name]);
        }
    }
}
