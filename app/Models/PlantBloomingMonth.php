<?php

namespace App\Models;

use App\Models\Concerns\HasModelLabel;
use Illuminate\Database\Eloquent\Model;

class PlantBloomingMonth extends Model
{
    use HasModelLabel;

    protected $fillable = [
        'plant_blooming_id',
        'index',
    ];

    public function plantBlooming()
    {
        return $this->belongsTo(PlantBlooming::class);
    }

    public static function getModelLabel(): string
    {
        return __('attributes.Months');
    }

    public static function getPluralModelLabel(): string
    {
        return __('attributes.Months');
    }
}
