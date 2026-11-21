<?php

namespace App\Models;

use App\Enums\GroundNutrition;
use App\Enums\HardinessZone;
use App\Enums\Moisture;
use App\Models\Concerns\HasModelLabel;
use App\Models\Concerns\ReplicatesRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantEnvironment extends Model
{
    use HasFactory;
    use ReplicatesRelations;
    use HasModelLabel;

    const REPLICATEABLE_RELATIONS = [
        'soilTypes',
        'sunExposures'
    ];

    protected $fillable = [
        'cultivar_id',
        'winter_hardiness',
        'ph_range',
        'ground_nutrition_minimum',
        'soil_moisture'
    ];

    protected $casts = [
        'winter_hardiness' => HardinessZone::class,
        'ph_range' => 'array',
        'ground_nutrition_minimum' => GroundNutrition::class,
        'soil_moisture' => Moisture::class
    ];

    public function cultivar(): BelongsTo
    {
        return $this->belongsTo(related: Cultivar::class);
    }

    public function soilTypes(): BelongsToMany
    {
        return $this->belongsToMany(related: SoilType::class);
    }

    public function sunExposures(): HasMany
    {
        return $this->hasMany(PlantEnvironmentSunExposure::class);
    }

    public static function getModelLabel(): string
    {
        return __('sections.environment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('sections.environments');
    }

}
