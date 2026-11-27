<?php

namespace App\Providers;

use App\Models\Color;
use App\Support\ColorTag;
use App\Support\NameDisplayPreferences;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(NameDisplayPreferences::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Select::configureUsing(function (Select $select): void {
            $select
                ->searchable();
        });

        TextInput::configureUsing(function (TextInput $textInput): void {
            $textInput
                ->maxLength(100)
                ->trim();
        });

        FilamentColor::register([
            'm0' => \Filament\Support\Colors\Color::Green,
            'm1' => \Filament\Support\Colors\Color::Yellow,
            'm2' => \Filament\Support\Colors\Color::Red,
        ]);

        if (! Schema::hasTable('colors')) {
            return;
        }

        // Register default colors for white, black, and gray
        $defaultColors = [
            'White' => \Filament\Support\Colors\Color::Slate,
            'Black' => \Filament\Support\Colors\Color::Stone,
            'Gray' => \Filament\Support\Colors\Color::Gray,
        ];

        FilamentColor::register(Color::catalog()->mapWithKeys(fn (array $color) => [
            ColorTag::name($color['name']) => $defaultColors[$color['name']] ?? $color['hex_value'],
        ])->all());

    }
}
