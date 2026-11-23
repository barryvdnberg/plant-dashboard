<?php

namespace App\Models;

use App\Models\Concerns\HasLocalization;
use App\Models\Concerns\HasModelLabel;
use App\Models\Concerns\HasScientificName;
use App\Models\Contracts\Localization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model implements Localization
{
    use HasFactory;
    use HasLocalization;
    use HasModelLabel;

    use HasScientificName;

    protected $fillable = [
        'name',
        'image_paths',
    ];

    protected $casts = [
        'image_paths' => 'array',
    ];

    public function genera(): HasMany
    {
        return $this->hasMany(related: Genus::class);
    }
}
