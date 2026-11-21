<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PlantType: string implements HasLabel
{
    // case TREE = 'tree';
    case SHRUB = 'shrub';
    case PERENNIAL = 'perennial';
    case ANNUAL = 'annual';
    case BIENNIAL = 'biennial';

    public function getLabel(): string|Htmlable|null
    {
         return match ($this) {
            // self::TREE => __('enums.plant_type.tree'),
            self::SHRUB => __('enums.plant_type.shrub'),
            self::PERENNIAL => __('enums.plant_type.perennial'),
            self::ANNUAL => __('enums.plant_type.annual'),
            self::BIENNIAL => __('enums.plant_type.biennial')
        };
    }
}
