<?php

namespace App\Models;

use App\Models\Concerns\HasColors;
use App\Models\Concerns\HasLocalization;
use App\Models\Concerns\HasLocalizedQuery;
use App\Models\Concerns\HasModelLabel;
use App\Models\Concerns\HasParent;
use App\Models\Concerns\ReplicatesRelations;
use App\Models\Contracts\Localization;
use App\Models\Contracts\ParentRelationship;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cultivar extends Model implements Localization, ParentRelationship
{
    use HasColors;
    use HasFactory;
    use HasLocalization;
    use HasLocalizedQuery;
    use HasModelLabel;
    use HasParent;
    use ReplicatesRelations;

    const REPLICATEABLE_RELATIONS = [
        'localizations',
        'colors',
        'properties',
        'foliage',
        'environment',
        'maintenance',
        'blooming',
    ];

    protected $fillable = [
        'plant_id',
        'name',
        'image_paths',
    ];

    protected $casts = [
        'image_paths' => 'array',
    ];

    public function setNameAttribute(?string $name): void
    {
        $this->attributes['name'] = blank($name) ? null : $name;
    }

    public function parent(): BelongsTo
    {
        return $this->plant();
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(related: Plant::class);
    }

    public function properties(): HasOne
    {
        return $this->hasOne(related: PlantProperties::class);
    }

    public function foliage(): HasOne
    {
        return $this->hasOne(related: Foliage::class);
    }

    public function environment(): HasOne
    {
        return $this->hasOne(related: PlantEnvironment::class);
    }

    public function maintenance(): HasOne
    {
        return $this->hasOne(related: PlantMaintenance::class);
    }

    public function blooming(): HasOne
    {
        return $this->hasOne(related: PlantBlooming::class);
    }

    public function gardens(): BelongsToMany
    {
        return $this->belongsToMany(related: Garden::class);
    }

    // probably not a very usefull relation as a fetch from the other side is much more common
    public function gardenPlants(): HasMany
    {
        return $this->hasMany(related: GardenPlant::class);
    }

    protected function localizedNameDepth(): int
    {
        return 1;
    }

    protected function missingParentName(): string
    {
        return __('Unknown :name', ['name' => Plant::getModelLabel()]);
    }

    public function getScientificNameAttribute(): string
    {
        $name = trim((string) $this->name);
        $parentName = $this->parent?->scientific_name ?: $this->missingParentName();

        if ($name === '') {
            return $parentName;
        }

        return trim($parentName." '".trim($name, "'\"")."'");
    }

    public function getLocalizedDisplayName(): string
    {
        return $this->getTranslation()
            ?? ($this->nameWithLocalizedParents($this->name ?? '') ?: $this->scientific_name)
            ?: __('Unknown :name', ['name' => __('attributes.Cultivar')]);
    }
}
