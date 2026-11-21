<?php

namespace Tests\Fixtures\Models;

use App\Models\Concerns\HasBelongsToProperties;
use App\Models\Concerns\HasLocalization;
use App\Models\Concerns\HasLocalizedQuery;
use App\Models\Contracts\Localization;
use App\Models\Species;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalizedPlantWithProperties extends Model implements Localization
{
    use HasBelongsToProperties;
    use HasLocalization;
    use HasLocalizedQuery;

    protected $table = 'plants';

    public const BELONGS_TO_PROPERTIES = ['species' => ['alias' => '_species', 'column' => 'name']];

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
}
