<?php

namespace App\Models;

use App\Models\Concerns\HasModelLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Garden extends Model
{
    use HasFactory;
    use HasModelLabel;

    protected $fillable = [
        'user_id',
        'name',
        'winter_hardiness',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }

    public function plants(): HasMany
    {
        return $this->hasMany(related: GardenPlant::class);
    }

    public function getPlantCountAttribute(): int
    {
        return $this->plants()->count();
    }

    public function getLowestPlantWinterHardiness()
    {
        // plants gets the list of gardenPlants in which we have the cultivars relation with cultivars and we need the the environment of those cultivars property winter_hardiness
        // so $ garden -> gardenPlant -> cultivar -> environment -> winter_hardiness
        return $this->plants()
            ->with('cultivar.environment')
            ->get()
            ->pluck('cultivar.environment.winter_hardiness')
            ->filter()
            ->min();
    }
}
