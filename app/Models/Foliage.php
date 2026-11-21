<?php

namespace App\Models;

use App\Models\Concerns\HasColors;
use App\Models\Concerns\HasModelLabel;
use App\Models\Concerns\ReplicatesRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Foliage extends Model
{
    use HasFactory;
    use ReplicatesRelations;
    use HasModelLabel;
    use HasColors;

    const REPLICATEABLE_RELATIONS = [
        'colors',
    ];

    protected $fillable = [
        'cultivar_id',
        'retention',
        'seasonal_color_change',
    ];

    public function cultivar(): BelongsTo
    {
        return $this->belongsTo(related: Cultivar::class);
    }

    public function colorsForSeason(string $season): MorphToMany
    {
        return $this->colors()->wherePivot('tag', $season);
    }

    public function defaultColors(): MorphToMany
    {
        return $this->colorsForSeason('default');
    }

    public function springColors(): MorphToMany
    {
        return $this->colorsForSeason('spring');
    }

    public function summerColors(): MorphToMany
    {
        return $this->colorsForSeason('summer');
    }

    public function fallColors(): MorphToMany
    {
        return $this->colorsForSeason('fall');
    }

    public function winterColors(): MorphToMany
    {
        return $this->colorsForSeason('winter');
    }
}
