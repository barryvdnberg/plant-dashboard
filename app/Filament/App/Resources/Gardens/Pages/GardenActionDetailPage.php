<?php

namespace App\Filament\App\Resources\Gardens\Pages;

use App\Enums\HardinessZone;
use App\Filament\App\Resources\GardenResource;
use App\Models\GardenPlant;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class GardenActionDetailPage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = GardenResource::class;

    protected string $view = 'filament.app.resources.gardens.pages.garden-action-detail-page';

    public GardenPlant $gardenPlant;

    public int $threshold = 6;

    public function mount(int|string $record, int|string $gardenPlant): void
    {
        $this->record = $this->resolveRecord($record);
        $this->authorize('view', $this->getRecord());

        $this->gardenPlant = GardenPlant::query()
            ->where('id', $gardenPlant)
            ->where('garden_id', $this->getRecord()->id)
            ->whereHas('garden', fn ($q) => $q->where('user_id', Auth::id()))
            ->with('cultivar.environment')
            ->firstOrFail();
    }

    public function hydrate(): void
    {
        $this->authorize('view', $this->getRecord());
        $this->authorize('view', $this->gardenPlant);
    }

    public function getTitle(): string
    {
        $name = $this->gardenPlant->name ?: $this->gardenPlant->cultivar?->name ?? __('Plant');

        return __('Action: :name', ['name' => $name]);
    }

    public function content(Schema $schema): Schema
    {
        $plant = $this->gardenPlant;
        $zone  = $plant->cultivar?->environment?->winter_hardiness;
        $diff  = $zone instanceof HardinessZone ? $this->threshold - $zone->value : null;

        $severity = match (true) {
            $diff === null || $diff <= 0 => 'success',
            $diff >= 3                   => 'danger',
            $diff === 2                  => 'warning',
            default                      => 'info',
        };

        $zoneName = $zone instanceof HardinessZone
            ? __('Zone :zone', ['zone' => $zone->getLabel()])
            : __('No zone set');

        $description = $diff !== null && $diff > 0
            ? __(':zone — :n zone(s) below the winter hardiness threshold of Zone :threshold.', [
                'zone'      => $zoneName,
                'n'         => $diff,
                'threshold' => $this->threshold,
            ])
            : __(':zone — this plant meets the current winter hardiness threshold.', ['zone' => $zoneName]);

        $name = $plant->name ?: $plant->cultivar?->name ?? __('Unknown');

        return $schema->components([
            Callout::make()
                ->heading($name)
                ->description($description)
                ->status($severity)
                ->columnSpanFull(),

            Section::make(__('Plant details'))
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextEntry::make('plant_name')
                        ->label(__('Name'))
                        ->state($name),

                    TextEntry::make('cultivar_name')
                        ->label(__('Cultivar'))
                        ->state($plant->cultivar?->name ?? __('—')),

                    TextEntry::make('zone')
                        ->label(__('Hardiness zone'))
                        ->state($zoneName)
                        ->badge()
                        ->color($severity),

                    TextEntry::make('threshold')
                        ->label(__('Current threshold'))
                        ->state(__('Zone :zone', ['zone' => $this->threshold]))
                        ->badge()
                        ->color('primary'),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_actions')
                ->label(__('Back to actions'))
                ->url(GardenResource::getUrl('actions', ['record' => $this->getRecord()]))
                ->color('gray'),
        ];
    }
}
