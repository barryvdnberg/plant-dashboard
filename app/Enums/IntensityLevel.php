<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum IntensityLevel: string implements HasLabel, HasColor
{
    case LOW = 'low';
    case MODERATE = 'moderate';
    case HIGH = 'high';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::LOW => __('enums.intensity_level.low'),
            self::MODERATE => __('enums.intensity_level.moderate'),
            self::HIGH => __('enums.intensity_level.high'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::LOW => 'gray',
            self::MODERATE => 'warning',
            self::HIGH => 'danger'
        };
    }
}
