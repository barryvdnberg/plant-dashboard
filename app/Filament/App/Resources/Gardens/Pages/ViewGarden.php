<?php

namespace App\Filament\App\Resources\Gardens\Pages;

use App\Enums\HardinessZone;
use App\Filament\App\Resources\GardenResource;
use App\Models\Garden;
use App\Models\GardenPlant;
use App\Models\Plant;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewGarden extends ViewRecord
{
    protected static string $resource = GardenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        $threshold = 6; // will later come from weather data

        $record = $this->getRecord();

        $plants = $record
            ->plants()
            ->with('cultivar.environment')
            ->get()
            ->map(fn (GardenPlant $p) => [
                'diff' => ($p->cultivar?->environment?->winter_hardiness instanceof HardinessZone)
                              ? $threshold - $p->cultivar->environment->winter_hardiness->value
                              : null,
            ]);

        $atRiskCount = $plants->filter(fn ($p) => ($p['diff'] ?? 0) > 0)->count();

        return $schema->components([
            // Summary strip
            Section::make(__('Overview'))
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    TextEntry::make('name')
                        ->label(Garden::getModelLabel()),

                    TextEntry::make('plants_count_stat')
                        ->label(__('All :plural', ['plural' => Plant::getPluralModelLabel()]))
                        ->state($plants->count())
                        ->badge()
                        ->color('primary'),

                    TextEntry::make('at_risk_count_stat')
                        ->label(__('At risk'))
                        ->state($atRiskCount)
                        ->badge()
                        ->color($atRiskCount > 0 ? 'danger' : 'success'),
                ]),

            // Navigation Plants page
            Callout::make()
                ->heading(__('Plants'))
                ->description(__('View all plants in this garden.'))
                ->status('info')
                ->actions([
                    \Filament\Actions\Action::make('go_plants')
                        ->label(__('View plants'))
                        ->url(GardenResource::getUrl('plants', ['record' => $record->id])),
                ])
                ->columnSpanFull(),

            // Navigation → Actions page
            Callout::make()
                ->heading(__('Recommended actions'))
                ->description($atRiskCount > 0
                    ? __(':n plant(s) need attention this winter.', ['n' => $atRiskCount])
                    : __('No actions needed — all plants are winter-hardy.')
                )
                ->status($atRiskCount > 0 ? 'warning' : 'success')
                ->actions([
                    \Filament\Actions\Action::make('go_actions')
                        ->label(__('View actions'))
                        ->url(GardenResource::getUrl('actions', ['record' => $record->id])),
                ])
                ->columnSpanFull(),
        ]);
    }
}
