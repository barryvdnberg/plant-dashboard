<?php

namespace App\Models;

use App\Models\Concerns\HasModelLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GardenPlant extends Model
{
    use HasFactory;
    use HasModelLabel;

    protected $fillable = [
        'cultivar_id',
        'garden_id',
        'name',
        'size',
    ];

    public function garden(): BelongsTo
    {
        return $this->belongsTo(related: Garden::class);
    }

    public function cultivar(): BelongsTo
    {
        return $this->belongsTo(related: Cultivar::class);
    }
}
