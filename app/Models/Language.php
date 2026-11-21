<?php

namespace App\Models;

use App\Models\Concerns\HasLocalization;
use App\Models\Concerns\HasModelLabel;
use App\Models\Contracts\Localization;
use App\Support\Languages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model implements Localization
{
    use HasFactory;
    use HasLocalization;
    use HasModelLabel;

    protected static function booted(): void
    {
        $invalidate = function (Language $language): void {
            Languages::clearCache();
            Color::forgetLocalizedLabels((int) $language->getKey());
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    protected $fillable = [
        'code',
        'name',
        'english_name',
    ];
}
