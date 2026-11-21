<?php

namespace App\Models;

use App\Models\Concerns\HasModelLabel;
use App\Models\Concerns\ReplicatesRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantProperties extends Model
{
    use HasFactory;
    use ReplicatesRelations;
    use HasModelLabel;

    const REPLICATEABLE_RELATIONS = [
        'plantTypes',
        'growthStyles',
        'gardenStyles',
        'traits'
    ];

    protected $fillable = [
        'cultivar_id',
        'plant_type',
    ];

    public function cultivar()
    {
        return $this->belongsTo(related: Cultivar::class);
    }   

    public function growthStyles(): HasMany
    {
        return $this->hasMany(PlantPropertiesGrowthStyle::class);
    }

    public function gardenStyles(): BelongsToMany
    {
        return $this->belongsToMany(related: GardenStyle::class);
    }

    public function traits(): HasMany
    {
        return $this->hasMany(PlantPropertiesTrait::class);
    }

    public static function getModelLabel(): string
    {
        return __('sections.properties');
    }

    public static function getPluralModelLabel(): string
    {
        return __('sections.properties_plural');
    }
}
