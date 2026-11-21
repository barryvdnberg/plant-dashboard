<?php

namespace App\Filament\App\Resources\Gardens\Pages;

use App\Enums\HardinessZone;
use App\Filament\App\Resources\GardenResource;
use App\Models\GardenPlant;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GardenPlantsPage extends ViewRecord
{
    protected static string $resource = GardenResource::class;

    public int $threshold = 6;

    public function getTitle(): string
    {
        return __('Plants');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('Back to garden'))
                ->url(GardenResource::getUrl('view', ['record' => $this->getRecord()]))
                ->color('gray'),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        $threshold = $this->threshold;

        return $schema->components([
            Section::make(__('All plants'))
                ->columnSpanFull()
                ->schema([
                    RepeatableEntry::make('plants')
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('Name'))
                                ->state(fn (GardenPlant $record) => $record->name ?: $record->cultivar?->name)
                                ->weight('bold'),

                            TextEntry::make('cultivar.environment.winter_hardiness')
                                ->label(__('Zone'))
                                ->badge()
                                ->color(function (GardenPlant $record) use ($threshold) {
                                    $zone = $record->cultivar?->environment?->winter_hardiness;
                                    if (! $zone instanceof HardinessZone) {
                                        return 'gray';
                                    }
                                    $diff = $threshold - $zone->value;

                                    return match (true) {
                                        $diff >= 3 => 'danger',
                                        $diff === 2 => 'warning',
                                        $diff === 1 => 'info',
                                        default     => 'success',
                                    };
                                })
                                ->formatStateUsing(fn ($state) => $state instanceof HardinessZone
                                    ? __('Zone :zone', ['zone' => $state->getLabel()])
                                    : __('—')
                                ),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
