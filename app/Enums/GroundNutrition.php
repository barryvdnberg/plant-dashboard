<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum GroundNutrition: string implements HasLabel
{
    case POOR = 'poor';
    case DECENT = 'decent';
    case RICH = 'rich';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::POOR => __('enums.ground_nutrition.poor'),
            self::DECENT => __('enums.ground_nutrition.decent'),
            self::RICH => __('enums.ground_nutrition.rich'),
        };
    }
}
