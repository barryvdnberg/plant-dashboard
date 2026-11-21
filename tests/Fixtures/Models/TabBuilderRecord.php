<?php

namespace Tests\Fixtures\Models;

use App\Models\Concerns\HasColors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class TabBuilderRecord extends Model
{
    use HasColors;

    protected $table = 'tab_builder_records';

    protected $guarded = [];

    protected $casts = ['items' => 'array'];

    public $timestamps = false;

    public function details(): HasOne
    {
        return $this->hasOne(TabBuilderDetail::class, 'record_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(TabBuilderDetail::class, 'owner_id');
    }

    public function palette(): MorphToMany
    {
        return $this->colors();
    }

    public function scopedColors(): MorphToMany
    {
        return $this->colors()->using(TabBuilderColorPivot::class)->withPivot('palette');
    }
}
