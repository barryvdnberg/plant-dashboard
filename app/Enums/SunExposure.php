<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum SunExposure: string implements HasLabel
{
    case SUN = 'sun';
    case HALF_SHADE = 'half shade';
    case SHADE = 'shade';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::SUN => __('enums.sun_exposure.sun'),
            self::HALF_SHADE => __('enums.sun_exposure.half_shade'),
            self::SHADE => __('enums.sun_exposure.shade'),
        };
    }
}
