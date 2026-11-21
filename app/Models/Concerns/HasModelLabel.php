<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasModelLabel
{
    public static function modelSectionName(): string
    {
        return Str::snake(class_basename(static::class));
    }
    
    public static function getModelLabel(): string
    {
        return __('sections.' . static::modelSectionName());
    }
    
    public static function getPluralModelLabel(): string
    {
        return __('sections.' . Str::plural(static::modelSectionName()));
    }

}