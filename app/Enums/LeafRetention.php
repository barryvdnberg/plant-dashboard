<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum LeafRetention: string implements HasLabel
{
    case EVERGREEN = 'evergreen';
    case DECIDUOUS = 'deciduous';
    case SEMI_EVERGREEN = 'semi-evergreen';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::EVERGREEN => __('enums.leaf_retention.evergreen'),
            self::DECIDUOUS => __('enums.leaf_retention.deciduous'),
            self::SEMI_EVERGREEN => __('enums.leaf_retention.semi-evergreen')
        };
    }
}
