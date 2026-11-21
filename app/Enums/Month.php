<?php

namespace App\Enums;

use App\Support\Contracts\HasShortLabel;
use Carbon\Carbon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;

enum Month: int implements HasLabel, HasShortLabel
{
    case JANUARY = 1;
    case FEBRUARY = 2;
    case MARCH = 3;
    case APRIL = 4;
    case MAY = 5;
    case JUNE = 6;
    case JULY = 7;
    case AUGUST = 8;
    case SEPTEMBER = 9;
    case OCTOBER = 10;
    case NOVEMBER = 11;
    case DECEMBER = 12;

    public function getLabel(): string|Htmlable|null
    {
        return Carbon::createFromDate(2025, $this->value, 1)
            ->locale(App::getLocale())
            ->translatedFormat('F');
    }


    public function getShortLabel(): string|Htmlable|null
    {
        return Carbon::createFromDate(2025, $this->value, 1)
            ->locale(App::getLocale())
            ->translatedFormat('M');
    }

    public function englishName(): string
    {
        return Carbon::createFromDate(2025, $this->value, 1)
            ->locale('en')
            ->translatedFormat('F');
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($month) => [
                $month->value => $month->getLabel(),
            ])
            ->toArray();
    }

    public static function searchableMap(): array
    {
        // both localized and English names
        return collect(self::cases())
            ->mapWithKeys(fn ($month) => [
                $month->value => [
                    strtolower($month->englishName()),
                    strtolower($month->getLabel()),
                ],
            ])
            ->toArray();
    }
}
