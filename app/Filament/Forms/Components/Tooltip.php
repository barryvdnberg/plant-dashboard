<?php

namespace App\Filament\Forms\Components;

use Filament\Schemas\Components\Icon;
use Filament\Support\Icons\Heroicon;

class Tooltip
{
    public static function make(string $description): Icon {
        return Icon::make(Heroicon::InformationCircle)
            ->tooltip($description)
            ->color('default');
    }
}
