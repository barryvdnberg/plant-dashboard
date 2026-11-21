<?php

namespace App\Filament\Forms\Builder\Partials;

use App\Enums\GrowthPattern;
use App\Enums\IntensityLevel;
use App\Enums\MaintenanceDifficulty;
use App\Enums\Month;
use App\Filament\Forms\Components\MatrixField;
use App\Filament\Forms\Components\TabRepeater;
use App\Filament\Forms\Components\Tooltip;
use App\Filament\Schemas\Schema as HideableSchema;
use App\Support\ColorTag;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;

class MaintenanceForm
{
    public static function form(): array
    {
        return [
            TabRepeater::make('maintenance')
                ->label(__('sections.maintenance'))
                ->hiddenLabel()
                ->relationship()
                ->options(GrowthPattern::class)
                ->disableOptionWhen(static::notInEnabledGrowthPatterns())
                ->keyFieldName('group')
                ->default([['group' => 'default']])
                ->schema(MaintenanceForm::getRepeaterContent())
                ->cloneable()
                ->itemDeletable(static fn (array $item): bool => ($item['group'] ?? null) !== 'default')
                ->itemEditable(static fn (array $item): bool => ($item['group'] ?? null) !== 'default')/** */
        ];
    }

    public static function getRepeaterContent(): array
    {
        return [
            Select::make('watering_needs')
                ->label(__('attributes.Watering_needs'))
                ->options(IntensityLevel::class)
                ->columnSpanFull(),

            MatrixField::make('shortening')
                ->layout(12, 1)
                ->label(__('attributes.shortening'))
                ->columnLabels(Month::class)
                ->colors([
                    ColorTag::cssClass('White'),
                    ColorTag::cssClass('Blue')
                ])
                ->size(Size::Large)
                ->afterLabel(Schema::start(Tooltip::make(__('Shows which months are suitable for pruning the plant back substantially.')))),

            MatrixField::make('recommendations')
                ->relationship()
                ->label(__('attributes.Recommendation_months'))
                ->columnLabels(Month::class)
                ->layout(12, 1)
                ->size(Size::Large)
                ->belowContent(self::generateMaintenanceMonthsSchema())
                ->afterLabel(Schema::start(Tooltip::make(__('Indicates whether a given month is suitable for applying maintenance tasks, such as pruning.')))),
                
            Toggle::make('maintenance_rich')
                ->label(__('attributes.Maintenance_rich'))
                ->columnSpanFull()
                ->afterLabel(Schema::start(Tooltip::make(__('Indicates whether this plant requires frequent care throughout the year.')))),

            Select::make('difficulty')
                ->label(__('attributes.Maintenance_difficulty'))
                ->options(MaintenanceDifficulty::class)
                ->columnSpanFull()
                ->afterLabel(Schema::start(Tooltip::make(__('How challenging each maintenance moment is.')))),
        ];
    }

    private static function generateMaintenanceMonthsSchema(): Schema
    {
        return HideableSchema::start([
            Icon::make(Heroicon::InformationCircle),
            Text::make(__('Create :type based on :name', [
                'type' => __('attributes.recommendation_months'),
                'name' => __('attributes.blooming_months'),
            ])),
            self::generateMaintenanceMonthsAction(),
        ])
            ->visibleJs(<<<'JS'
                !!$get('../../blooming.months')?.length &&
                Object.values($state ?? {}).every(v => v == 0)
            JS);
    }

    private static function generateMaintenanceMonthsAction(): Action
    {
        return Action::make(__('Generate'))
            ->action(function (MatrixField $component) {
                $months = (array) $component->getContainer()->getParentComponent()->getState() ?? [];

                $values = array_map('intval', (array) $component->getState());

                foreach ($months as $month) {
                    if ($month instanceof Month) {
                        $month = $month->value;
                    }

                    $month = (int) $month;

                    $values[($month + 11) % 12] = 2;

                    $previous = ($month + 10) % 12;
                    if (($values[$previous] ?? 0) < 2) {
                        $values[$previous] = 1;
                    }
                    $next = $month % 12;
                    if (($values[$next] ?? 0) < 2) {
                        $values[$next] = 1;
                    }
                }

                $component->state($values);
            });
    } 

    private static ?array $enabledGrowthPatterns = null;

    private static function enabledGrowthPatterns(Get $get): array
    {
        if (static::$enabledGrowthPatterns === null) {
            $enabledEnums = collect($get('properties.growth_styles'))->mapWithKeys(fn (GrowthPattern $pattern) => [
                $pattern->value => $pattern->getLabel(),
            ])->all();

            if (! in_array('default', $enabledEnums)) {
                $enabledEnums['default'] = __('enums.growth_pattern.default');
            }

            static::$enabledGrowthPatterns = $enabledEnums;
        }

        return static::$enabledGrowthPatterns ?? [];
    }

    private static function notInEnabledGrowthPatterns(): Closure
    {
        return function (Get $get, null|string $value) {
            return ! in_array($value, array_keys(static::enabledGrowthPatterns($get)));
        };
    }
}
