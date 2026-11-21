<?php

namespace App\Models;

use App\Models\Concerns\HasLocalization;
use App\Models\Concerns\HasModelLabel;
use App\Models\Contracts\CachesLocalizedLabels;
use App\Models\Contracts\Localization;
use App\Support\Languages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Color extends Model implements CachesLocalizedLabels, Localization
{
    use HasFactory;
    use HasLocalization;
    use HasModelLabel;

    public const CATALOG_CACHE_KEY = 'colors:catalog';

    protected $fillable = [
        'name',
        'hex_value',
    ];

    protected static function booted(): void
    {
        $invalidate = function () {
            Cache::memo()->forget(static::CATALOG_CACHE_KEY);

            static::forgetLocalizedLabels();
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    public function cultivars()
    {
        return $this->morphedByMany(Cultivar::class, 'colorable')
            ->using(Colorable::class)
            ->withPivot(['id', 'tag']);
    }

    public function foliages()
    {
        return $this->morphedByMany(Foliage::class, 'colorable')
            ->using(Colorable::class)
            ->withPivot(['id', 'tag']);
    }

    public function colorables()
    {
        return $this->hasMany(Colorable::class);
    }

    public static function cacheKey(int $languageId): string
    {
        return "colors:labels:v2:{$languageId}";
    }

    public static function forgetLocalizedLabels(?int $languageId = null): void
    {
        $languageIds = $languageId === null ? Language::query()->pluck('id') : [$languageId];

        foreach ($languageIds as $id) {
            Cache::memo()->forget(static::cacheKey((int) $id));
        }
    }

    /** All color definitions, shared by palette registration and form rendering. */
    public static function catalog(): Collection
    {
        return collect(Cache::memo()->rememberForever(
            static::CATALOG_CACHE_KEY,
            fn () => static::query()->get(['id', 'name', 'hex_value'])->toArray(),
        ));
    }

    public static function localizedMap(?int $languageId = null): Collection
    {
        $languageId = $languageId ?? Languages::localeId();

        if ($languageId === null) {
            return static::catalog()->pluck('name', 'id');
        }

        $key = static::cacheKey($languageId);

        return collect(Cache::memo()->rememberForever($key, function () use ($languageId) {
            $language = Language::query()->findOrFail($languageId);

            return static::query()->withLocalization(locale: $language->code)
                ->select('colors.id')
                ->addLocalizationCoalesce()
                ->pluck('_label', 'id')->all();
        }));
    }
}
