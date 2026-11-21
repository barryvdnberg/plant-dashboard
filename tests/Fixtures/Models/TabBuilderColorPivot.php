<?php

namespace Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class TabBuilderColorPivot extends MorphPivot
{
    protected $table = 'colorables';

    protected $guarded = [];
}
