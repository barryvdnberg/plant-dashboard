<?php

namespace App\Filament\Forms\Builder\Contracts;

use Filament\Schemas\Components\Component;

interface ParentField
{
    public static function parentField(): Component;
}