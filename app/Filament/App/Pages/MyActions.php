<?php

namespace App\Filament\App\Pages;

use App\Enums\HardinessZone;
use App\Filament\App\Resources\GardenResource;
use App\Models\Garden;
use App\Models\GardenPlant;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MyActions extends Page
{
    protected string $view = 'filament.app.pages.my-actions';

    protected static string|null $title = null;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static ?int $navigationSort = -10; // pin to top of nav

    public int $threshold = 6;

    public function getTitle(): string
    {
        return __('My actions');
    }

    public static function getNavigationLabel(): string
    {
        return __('My actions');
    }

    /**
     * Collect all at-risk plants across every garden belonging to the user,
     * keyed by garden.
     *
     * @return Collection<int, array{garden: Garden, plants: Collection}>
     */
    protected function getAtRiskByGarden(): Collection
    {
        $threshold = $this->threshold;

        return Garden::query()
            ->where('user_id', Auth::id())
            ->with(['plants.cultivar.environment'])
            ->get()
            ->map(function (Garden $garden) use ($threshold) {
                $atRisk = $garden->plants
                    ->map(fn (GardenPlant $p) => [
                        'id'   => $p->id,
                        'name' => $p->name ?: $p->cultivar?->name,
                        'zone' => $p->cultivar?->environment?->winter_hardiness,
                        'diff' => ($p->cultivar?->environment?->winter_hardiness instanceof HardinessZone)
                                      ? $threshold - $p->cultivar->environment->winter_hardiness->value
                                      : null,
                    ])
                    ->filter(fn ($p) => ($p['diff'] ?? 0) > 0)
                    ->sortByDesc('diff')
                    ->values();

                return ['garden' => $garden, 'plants' => $atRisk];
            })
            ->filter(fn ($row) => $row['plants']->isNotEmpty())
            ->values();
    }

    public function content(Schema $schema): Schema
    {
        $atRiskByGarden = $this->getAtRiskByGarden();

        $components = [];

        if ($atRiskByGarden->isEmpty()) {
            $components[] = Callout::make()
                ->heading(__('All clear'))
                ->description(__('None of your plants need winter hardiness attention right now.'))
                ->status('success')
                ->columnSpanFull();
        } else {
            foreach ($atRiskByGarden as ['garden' => $garden, 'plants' => $plants]) {
                $callouts = $plants->map(function (array $plant) use ($garden) {
                    $diff     = $plant['diff'];
                    $severity = match (true) {
                        $diff >= 3 => 'danger',
                        $diff === 2 => 'warning',
                        default     => 'info',
                    };

                    $zoneName = $plant['zone'] instanceof HardinessZone
                        ? __('Zone :zone', ['zone' => $plant['zone']->getLabel()])
                        : __('No zone set');

                    return Callout::make()
                        ->heading($plant['name'])
                        ->description(__(':zone — :n zone(s) below the winter threshold', [
                            'zone' => $zoneName,
                            'n'    => $diff,
                        ]))
                        ->status($severity)
                        ->actions([
                            Action::make('detail_' . $plant['id'])
                                ->label(__('View details'))
                                ->url(GardenResource::getUrl('action-detail', [
                                    'record'      => $garden->id,
                                    'gardenPlant' => $plant['id'],
                                ])),
                        ]);
                })->values()->all();

                $components[] = Section::make($garden->name)
                    ->description(__(':n plant(s) need attention', ['n' => count($callouts)]))
                    ->headerActions([
                        Action::make('view_garden_' . $garden->id)
                            ->label(__('View garden'))
                            ->url(GardenResource::getUrl('view', ['record' => $garden->id]))
                            ->color('gray'),
                    ])
                    ->schema($callouts)
                    ->columns(2)
                    ->columnSpanFull();
            }
        }

        return $schema->components($components);
    }
}
