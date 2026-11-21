<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantPropertiesTrait extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_properties_id',
        'trait_index'
    ];

    public function plantProperties(): BelongsTo
    {
        return $this->belongsTo(related: PlantProperties::class);
    }
}
