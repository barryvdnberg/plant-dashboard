<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Season: string implements HasLabel
{
    case SPRING = 'spring';
    case SUMMER = 'summer';
    case FALL = 'fall';
    case WINTER = 'winter';

    public function getLabel(): string|Htmlable|null
    {
        return ucfirst($this->value);
    }
}
