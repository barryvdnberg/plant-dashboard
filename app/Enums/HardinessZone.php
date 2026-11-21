<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum HardinessZone: int implements HasLabel
{
    case ZONE_1 = 1;
    case ZONE_2 = 2;
    case ZONE_3 = 3;
    case ZONE_4 = 4;
    case ZONE_5 = 5;
    case ZONE_6 = 6;
    case ZONE_7 = 7;
    case ZONE_8 = 8;
    case ZONE_9 = 9;
    case ZONE_10 = 10;
    case ZONE_11 = 11;
    case ZONE_12 = 12;
    case ZONE_13 = 13;

    public function getLabel(): string|Htmlable|null
    {
        return $this->value;
    }
}
