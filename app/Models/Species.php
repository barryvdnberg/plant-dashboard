<?php

namespace App\Models;

use App\Models\Concerns\HasLocalization;
use App\Models\Concerns\HasLocalizedQuery;
use App\Models\Concerns\HasModelLabel;
use App\Models\Concerns\HasParent;
use App\Models\Concerns\HasScientificName;
use App\Models\Contracts\Localization;
use App\Models\Contracts\ParentRelationship;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Species extends Model implements Localization, ParentRelationship
{
    use HasFactory;
    use HasLocalization;
    use HasLocalizedQuery;
    use HasModelLabel;
    use HasParent;
    use HasScientificName;

    protected $fillable = [
        'genus_id',
        'name',
        'image_paths',
    ];

    protected function scientificNameDepth(): int
    {
        return 1;
    }

    protected $casts = [
        'image_paths' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->genus();
    }

    public function genus(): BelongsTo
    {
        return $this->belongsTo(related: Genus::class);
    }

    public function plants(): HasMany
    {
        return $this->hasMany(related: Plant::class);
    }

    public static function getPluralModelLabel(): string
    {
        return __('sections.species_plural');
    }
}
