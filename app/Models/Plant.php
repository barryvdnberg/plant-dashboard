<?php

namespace App\Models;

use App\Models\Concerns\HasLocalization;
use App\Models\Concerns\HasLocalizedQuery;
use App\Models\Concerns\HasModelLabel;
use App\Models\Concerns\HasParent;
use App\Models\Concerns\HasScientificName;
use App\Models\Contracts\Localization;
use App\Models\Contracts\ParentRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Plant extends Model implements Localization, ParentRelationship
{
    use HasFactory;
    use HasLocalization;
    use HasLocalizedQuery;
    use HasModelLabel;
    use HasParent;
    use HasScientificName;

    protected $fillable = [
        'species_id',
        'name',
        'image_paths',
    ];

    protected $casts = [
        'image_paths' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->species();
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(related: Species::class);
    }

    public function allCultivars(): HasMany
    {
        return $this->hasMany(related: Cultivar::class);
    }

    public function baseCultivar(): HasOne
    {
        return $this->hasOne(related: Cultivar::class)
            ->where(fn (Builder $query) => $query->whereNull('name')->orWhere('name', ''));
    }

    public function cultivars(): HasMany
    {
        return $this->allCultivars()->whereNotNull('name')->where('name', '!=', '');
    }
}
