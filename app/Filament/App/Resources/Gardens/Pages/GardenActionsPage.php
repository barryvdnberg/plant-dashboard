<?php

namespace App\Filament\App\Resources\Gardens\Pages;

use App\Enums\HardinessZone;
use App\Filament\App\Resources\GardenResource;
use App\Models\GardenPlant;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Number;

class GardenActionsPage extends ViewRecord
{
    protected static string $resource = GardenResource::class;

    public int $threshold = 6;

    public function getTitle(): string
    {
        return __('Recommended actions');
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
        $record = $this->getRecord();

        $atRisk = $record->plants()
            ->with('cultivar.environment')
            ->get()
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

        $components = [];

        $components[] = Section::make(__('Overview'))
            ->columnSpanFull()
            ->columns(2)
            ->schema([
                TextEntry::make('at_risk_count')
                    ->label(__('At risk'))
                    ->state($atRisk->count())
                    ->badge()
                    ->color($atRisk->count() > 0 ? 'danger' : 'success'),

                TextEntry::make('threshold_display')
                    ->label(__('Current threshold'))
                    ->state(__('Zone :zone', ['zone' => $threshold]))
                    ->badge()
                    ->color('primary'),
            ]);

        if ($atRisk->isEmpty()) {
            $components[] = Callout::make()
                ->heading(__('No actions needed'))
                ->description(__('All plants meet the current winter hardiness threshold.'))
                ->status('success')
                ->columnSpanFull();
        } else {
            // Group plants by how many zones below threshold they are (desc: most severe first)
            $groups = $atRisk->groupBy('diff')->sortKeysDesc();

            $components[] = Section::make(__('Recommended actions'))
                ->schema(function () use ($groups) {
                    $sectionComponents = [];
                    
                    foreach ($groups as $diff => $plants) {
                        $severity = match (true) {
                            $diff >= 2 => 'danger',
                            $diff === 1 => 'warning',
                            default => 'info',
                        };

                        $sectionComponents[] = Callout::make()
                            ->heading(function () use ($diff) {
                                return Number::spell($diff) . " zone(s) below threshold";
                            })
                            ->description(function () use ($diff) {
                                if ($diff == 1) {
                                    return __('These plants are below the winter hardiness threshold. Consider providing some winter protection, such as mulching or using a frost cloth.');
                                }
                                else {
                                    return __('These plants are significantly below the winter hardiness threshold. It is highly recommended to take action to protect them during the winter, such as moving them to a more sheltered location.');
                                }
                            })    
                            ->status($severity)
                            ->controlActions([
                                Action::make('dismiss')
                                    ->icon(Heroicon::XMark)
                                    ->iconButton()
                                    ->color('gray')
                                    ->action(function () use ($diff) {
                                        return session()->put('hardiness_action_'. $diff . '_dismissed', true);
                                    })
                            ])
                            ->footer([
                                Action::make('detail_' . $diff)
                                    ->label(__('View all plants'))
                                    ->url('#'),
                                Text::make(function () use ($plants) {
                                    $names = [];
                                    foreach ($plants as $plant) {
                                        $names[] = $plant['name'];
                                    }

                                    // count the amount of characters in the names, and if it's over 40, we'll end it with ...
                                    // check per plant, if adding the name would put it over 40 characters, we end it with ... instead of adding the name
                                    // don't forget to check if there actually is another name to add if we will end with ...
                                    $namesString = '';
                                    foreach ($plants as $index => $plant) {
                                        $name = $plant['name'];
                                        if (strlen($namesString) + strlen($name) > 40) {
                                            if ($index < count($plants) - 1) {
                                                $namesString .= '...';
                                            }
                                            break;
                                        }
                                        if ($index > 0) {
                                            $namesString .= ', ';
                                        }
                                        $namesString .= $name;
                                    }
                                    
                                    return $namesString;
                                }),
                            ]);
                    }
                    return $sectionComponents;
                })
                ->description(__('These notifications may require you to take action.'))
                ->columnSpanFull();

        }

        return $schema->components($components);
    }
}
