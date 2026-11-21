<?php

namespace App\Models;

use App\Models\Concerns\HasColors;
use App\Models\Concerns\HasModelLabel;
use App\Models\Concerns\ReplicatesRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantBlooming extends Model
{
    use HasFactory;
    use ReplicatesRelations;
    use HasModelLabel;
    use HasColors;

    const REPLICATEABLE_RELATIONS = [
        'colors',
        'months'
    ];

    protected $fillable = [
        'cultivar_id',
    ];

    public function cultivar(): BelongsTo
    {
        return $this->belongsTo(related: Cultivar::class);
    }

    public function months()
    {
        return $this->hasMany(PlantBloomingMonth::class);
    }

    public static function getModelLabel(): string
    {
        return __('sections.blooming');
    }

    public static function getPluralModelLabel(): string
    {
        return __('sections.bloomings');
    }
}
