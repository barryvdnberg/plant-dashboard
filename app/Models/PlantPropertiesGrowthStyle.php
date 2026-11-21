<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantPropertiesGrowthStyle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function plantProperties(): BelongsTo
    {
        return $this->belongsTo(PlantProperties::class);
    }
}
