<?php

namespace App\Filament\Resources\Gardens\Widgets;

use App\Enums\HardinessZone;
use App\Models\Garden;
use Filament\Widgets\Widget;

class LowWinterHardinessWarning extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.resources.gardens.widgets.low-winter-hardiness-warning';

    protected int|string|array $columnSpan = 'full';

    public Garden $record;

    /**
     * The hardiness zone threshold. Plants with a zone value below this are flagged.
     * Will eventually be replaced by a daily-pulled weather-based value.
     */
    public int $threshold = 4;

    protected function getViewData(): array
    {
        $plants = $this->record
            ->plants()
            ->with('cultivar.environment')
            ->get()
            ->filter(function ($gardenPlant) {
                $zone = $gardenPlant->cultivar?->environment?->winter_hardiness;

                return $zone instanceof HardinessZone && $zone->value < $this->threshold;
            });

        return [
            'plants' => $plants,
            'threshold' => HardinessZone::from($this->threshold),
        ];
    }
}
