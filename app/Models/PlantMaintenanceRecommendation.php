<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantMaintenanceRecommendation extends Model
{
    public $fillable = ['cultivar_id', 'position', 'value'];

    public function maintenance()
    {
        return $this->belongsTo(PlantMaintenance::class);
    }
}
