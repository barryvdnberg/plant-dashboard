<?php

namespace Tests\Fixtures\Models;

use App\Models\Concerns\HasColors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TabBuilderDetail extends Model
{
    use HasColors;

    protected $table = 'tab_builder_details';

    protected $guarded = [];

    protected $casts = ['items' => 'array'];

    public $timestamps = false;

    public function details(): HasOne
    {
        return $this->hasOne(self::class, 'parent_id');
    }
}
