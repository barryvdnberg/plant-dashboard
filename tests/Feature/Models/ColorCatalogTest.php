<?php

use App\Models\Color;
use App\Providers\AppServiceProvider;
use App\Support\ColorTag;
use Filament\Support\Colors\Color as Palette;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

it('loads the catalog once and reuses it for CSS classes and subsequent requests', function (): void {
    $red = Color::factory()->create(['name' => 'Red', 'hex_value' => '#ff0000']);
    Cache::memo()->forget(Color::CATALOG_CACHE_KEY);

    DB::enableQueryLog();
    DB::flushQueryLog();

    try {
        expect(Color::catalog()->first()['name'])->toBe('Red')
            ->and(ColorTag::cssClasses())->toBe([$red->id => 'fi-color-custom-red']);

        // Drop only the request memo, keeping the persistent cache intact.
        app()->forgetInstance('cache.__memoized:array');

        expect(ColorTag::cssClasses())->toBe([$red->id => 'fi-color-custom-red'])
            ->and(DB::getQueryLog())->toHaveCount(1);
    } finally {
        DB::disableQueryLog();
    }
});

it('invalidates cached definitions when colors are created updated or deleted', function (): void {
    expect(Color::catalog())->toBeEmpty();

    $color = Color::factory()->create(['name' => 'Red', 'hex_value' => '#ff0000']);
    expect(ColorTag::cssClasses())->toBe([$color->id => 'fi-color-custom-red']);

    $color->update(['name' => 'Dark Red', 'hex_value' => '#880000']);
    expect(ColorTag::cssClasses())->toBe([$color->id => 'fi-color-custom-dark-red'])
        ->and(Color::catalog()->first()['hex_value'])->toBe('#880000');

    $color->delete();
    expect(ColorTag::cssClasses())->toBeEmpty();
});

it('registers named palettes and preserves the neutral color overrides', function (): void {
    Color::factory()->create(['name' => 'Red', 'hex_value' => '#ff0000']);
    Color::factory()->create(['name' => 'White', 'hex_value' => '#ffffff']);

    (new AppServiceProvider(app()))->boot();

    expect(FilamentColor::getColor('custom-red'))->toBe(Palette::generatePalette('#ff0000'))
        ->and(FilamentColor::getColor('custom-white'))->toBe(Palette::Slate);
});
