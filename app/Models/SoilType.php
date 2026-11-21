<?php

namespace App\Models;

use App\Models\Concerns\HasLocalization;
use App\Models\Concerns\HasModelLabel;
use App\Models\Contracts\Localization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SoilType extends Model implements Localization
{
    use HasFactory;
    use HasLocalization;
    use HasModelLabel;

    protected $fillable = [
        'name',
        'image_path'
    ];

    public function plantEnvironments(): BelongsToMany
    {
        return $this->belongsToMany(related: PlantEnvironment::class);
    }
}
