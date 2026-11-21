<?php

namespace App\Models\Concerns;

use App\Models\Color;
use App\Models\Colorable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasColors
{
    public static function bootHasColors()
    {
        static::deleting(function ($model) {
            $model->colors()->detach();
        });
    }
    
    public function colors(): MorphToMany
    {
        return $this->morphToMany(Color::class, 'colorable')
            ->using(Colorable::class)
            ->withPivot(['id', 'tag']);
    }
}