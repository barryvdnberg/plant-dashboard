<?php

namespace App\Filament\Forms\Builder\Partials;

use App\Enums\Month;
use App\Filament\Forms\Components\ColorSelect;
use App\Filament\Forms\Components\HasManySelect;
use App\Filament\Forms\Components\Tooltip;
use Filament\Schemas\Schema;

final class BloomingForm
{
    public static function form(): array
    {
        return [
            HasManySelect::make('months')
                ->options(Month::class)
                ->table('months', 'index')
                ->afterLabel(Schema::start(Tooltip::make(__('Select the months in which this plant typically produces flowers.')))),
            ColorSelect::make('colors')
        ];
    }
}
