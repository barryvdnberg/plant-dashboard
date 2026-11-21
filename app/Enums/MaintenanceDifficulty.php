<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum MaintenanceDifficulty: string implements HasLabel
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::BEGINNER => __('enums.maintenance_difficulty.beginner'),
            self::INTERMEDIATE => __('enums.maintenance_difficulty.intermediate'),
            self::ADVANCED => __('enums.maintenance_difficulty.advanced'),
        };
    }
}
