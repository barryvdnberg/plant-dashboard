<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantEnvironmentSunExposure extends Model
{
    protected $fillable = [
        'value'
    ];

    public function plantEnvironment(): BelongsTo
    {
        return $this->belongsTo(PlantEnvironment::class);
    }
}
