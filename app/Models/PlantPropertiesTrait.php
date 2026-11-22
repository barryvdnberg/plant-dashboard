<?php

namespace App\Models;

use App\Enums\PlantTrait;
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

    protected function casts(): array
    {
        return [
            'trait_index' => PlantTrait::class,
        ];
    }

    public function plantProperties(): BelongsTo
    {
        return $this->belongsTo(related: PlantProperties::class);
    }
}
