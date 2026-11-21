<?php

namespace App\Support;

use App\Models\Language;

class Languages
{
    protected static ?array $map = null;

    public static function clearCache(): void
    {
        self::$map = null;
    }

    /**
     * Get the locale ID for a given locale code or the current locale if none is provided.
     */
    public static function localeId(?string $code = null): ?int
    {
        if ($code === null) {
            $code = static::locale();
        }

        self::$map ??= Language::pluck('id', 'code')->toArray();

        // An unsupported language has no translation; callers fall back to the stored name.
        return self::$map[$code] ?? null;
    }

    /**
     * Get the current locale code.
     */
    public static function locale(): string
    {
        return app()->getLocale();
    }
}
