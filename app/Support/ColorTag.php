<?php

namespace App\Support;

use App\Models\Color;
use Illuminate\Support\Str;

class ColorTag
{
    public const PREFIX = 'custom-';

    public static function name(string $colorName): string
    {
        return self::PREFIX.Str::slug($colorName);
    }

    public static function cssClass(string $colorName): string
    {
        return 'fi-color-'.self::name($colorName);
    }

    public static function cssClasses(): array
    {
        return Color::catalog()->mapWithKeys(fn (array $color) => [
            $color['id'] => self::cssClass($color['name']),
        ])->all();
    }
}
