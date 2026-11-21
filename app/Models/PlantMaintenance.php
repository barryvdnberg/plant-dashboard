<?php

namespace App\Models;

use App\Enums\GrowthPattern;
use App\Enums\IntensityLevel;
use App\Enums\MaintenanceDifficulty;
use App\Models\Concerns\HasModelLabel;
use App\Models\Concerns\ReplicatesRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantMaintenance extends Model
{
    use HasFactory;
    use ReplicatesRelations;
    use HasModelLabel;

    const REPLICATEABLE_RELATIONS = [
        'recommendations'
    ];

    protected $fillable = [
        'cultivar_id',
        'group',
        'watering_needs',
        'maintenance_rich',
        'difficulty',
        'test_column',
        'shortening'
    ];

    protected $casts = [
        'group' => GrowthPattern::class,
        'watering_needs' => IntensityLevel::class,
        'maintenance_rich' => 'boolean',
        'difficulty' => MaintenanceDifficulty::class,
        'shortening' => 'array',
    ];

    public function cultivar(): BelongsTo
    {
        return $this->belongsTo(related: Cultivar::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(related: PlantMaintenanceRecommendation::class);
    }

    public static function getModelLabel(): string
    {
        return __('sections.maintenance');
    }

    public static function getPluralModelLabel(): string
    {
        return __('sections.maintenances');
    }
}
