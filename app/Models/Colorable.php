<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Colorable extends MorphPivot
{
    protected $table = 'colorables';

    protected $fillable = [
        'color_id',
        'colorable_type',
        'colorable_id',
        'tag',
    ];
    
    public function colorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }
}
