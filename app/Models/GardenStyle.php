<?php

namespace App\Models;

use App\Models\Concerns\HasLocalization;
use App\Models\Concerns\HasModelLabel;
use App\Models\Contracts\Localization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GardenStyle extends Model implements Localization
{
    use HasFactory;
    use HasLocalization;
    use HasModelLabel;

    protected $fillable = [
        'name',
        'image_paths'
    ];

    protected $casts = [
        'image_paths' => 'array'
    ];

    public function plantProperties(): BelongsToMany
    {
        return $this->belongsToMany(related: PlantProperties::class);
    }
}
