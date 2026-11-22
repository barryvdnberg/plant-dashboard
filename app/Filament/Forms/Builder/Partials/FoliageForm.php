<?php

namespace App\Filament\Forms\Builder\Partials;

use App\Enums\LeafRetention;
use App\Filament\Forms\Components\ColorSelect;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;

final class FoliageForm
{
    public static function form(): array
    {
        return [
            Select::make('retention')
                ->label(__('attributes.Retention'))
                ->options(LeafRetention::class),

            ColorSelect::make('default_colors')
                ->label(__('attributes.Leaf_colors'))
                ->pivotData(['tag' => 'default']),

            CheckBox::make('seasonal_color_change')
                ->label(__('attributes.Seasonal_color_change')),

            Group::make([
                ColorSelect::make('spring_colors')
                    ->label(__('attributes.Spring_colors'))
                    ->pivotData(['tag' => 'spring']),

                ColorSelect::make('summer_colors')
                    ->label(__('attributes.Summer_colors'))
                    ->pivotData(['tag' => 'summer']),
                    
                ColorSelect::make('fall_colors')
                    ->label(__('attributes.Fall_colors'))
                    ->pivotData(['tag' => 'fall']),

                ColorSelect::make('winter_colors')
                    ->label(__('attributes.Winter_colors'))
                    ->pivotData(['tag' => 'winter']),

            ])
                ->visibleJs(<<<'JS'
                    $get('seasonal_color_change')
                JS),
        ];
    }
}
