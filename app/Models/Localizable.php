<?php

namespace App\Models;

use App\Models\Concerns\HasModelLabel;
use App\Models\Contracts\CachesLocalizedLabels;
use App\Models\Contracts\Localization;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\File;

class Localizable extends MorphPivot
{
    use HasModelLabel;

    protected $table = 'localizables';

    public $incrementing = true;

    protected static function booted(): void
    {
        $invalidateLabels = function (Localizable $localization): void {
            foreach ([$localization->getAttributes(), $localization->getRawOriginal()] as $attributes) {
                $type = $attributes['localizable_type'] ?? null;
                $class = $type ? (Relation::getMorphedModel($type) ?? $type) : null;

                if ($class && is_a($class, CachesLocalizedLabels::class, true)) {
                    $class::forgetLocalizedLabels((int) $attributes['language_id']);
                }
            }
        };

        static::saved($invalidateLabels);
        static::deleted($invalidateLabels);
    }

    protected $fillable = [
        'language_id',
        'value',
        'synonyms',
        'field',
        'sort',
    ];

    public function localizable(): MorphTo
    {
        return $this->morphTo();
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public static function getImplementedModels(): array
    {
        $model_path = app_path('Models');
        $model_files = File::allFiles($model_path);

        $models = collect($model_files)->map(function ($file) {
            return 'App\\Models\\'.$file->getFilenameWithoutExtension();
        })->filter(function ($class) {
            if (! class_exists($class)) {
                return false;
            }
            // check if it implements Localization interface
            if ((new $class) instanceof Localization) {
                return true;
            }

            return false;
        });

        return $models->values()->toArray();
    }
}
