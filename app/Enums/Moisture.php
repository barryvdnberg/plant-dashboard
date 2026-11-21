<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Moisture: string implements HasLabel
{
    case DRY = 'dry';
    case DAMP = 'damp';
    case WET = 'wet';

    public function getLabel(): string|Htmlable|null
    {
         return match ($this) {
            self::DRY => __('enums.moisture.dry'),
            self::DAMP => __('enums.moisture.damp'),
            self::WET => __('enums.moisture.wet'),
        };
    }
}
