<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Cultivars\CultivarResource;
use App\Filament\Resources\Families\FamilyResource;
use App\Filament\Resources\Genera\GenusResource;
use App\Filament\Resources\Plants\PlantResource;
use App\Filament\Resources\Species\SpeciesResource;
use App\Models\Cultivar;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\Species;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Contracts\Support\Htmlable;

class CreationChart extends ChartWidget
{
    use HasFiltersSchema;

    public function getHeading(): string | Htmlable | null
    {
        return __('Created Overview');
    }

    protected ?string $pollingInterval = '30s';

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('start_date')
                ->label(__('Start Date'))
                ->default(now()->subDays(6)),
            DatePicker::make('end_date')
                ->label(__('End Date'))
                ->default(now()),
        ]);
    }

    protected function getData(): array
    {
        
        $startDate = isset($this->filters['startDate'])
            ? Carbon::parse($this->filters['startDate'])->startOfDay()
            : now()->subDays(6)->startOfDay();

        $endDate = isset($this->filters['endDate'])
            ? Carbon::parse($this->filters['endDate'])->endOfDay()
            : now()->endOfDay();
        $cultivars = Trend::model(Cultivar::class)
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->perDay()
            ->count();

        $plants = Trend::model(Plant::class)
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->perDay()
            ->count();

        $species = Trend::model(Species::class)
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->perDay()
            ->count();

        $genera = Trend::model(Genus::class)
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->perDay()
            ->count();

        $families = Trend::model(Family::class)
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => CultivarResource::getPluralModelLabel(),
                    'data' => $cultivars->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
                [
                    'label' => PlantResource::getPluralModelLabel(),
                    'data' => $plants->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#ff5353ff',
                    'borderColor' => '#ff9999ff',
                ],
                [
                    'label' => SpeciesResource::getPluralModelLabel(),
                    'data' => $species->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#4bc0a3ff',
                    'borderColor' => '#96e0c1ff',
                ],
                [
                    'label' => GenusResource::getPluralModelLabel(),
                    'data' => $genera->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#FFCE56',
                    'borderColor' => '#FFE699',
                ],
                [
                    'label' => FamilyResource::getPluralModelLabel(),
                    'data' => $families->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#9966FF',
                    'borderColor' => '#BFA6FF',
                ]
            ],
            'labels' => $cultivars->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
