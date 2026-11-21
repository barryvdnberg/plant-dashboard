<?php

use App\Enums\NameDisplay;
use App\Enums\NameDisplayGroup;
use App\Filament\Forms\Components\ModelSelects\CultivarSelect;
use App\Filament\Forms\Components\ModelSelects\PlantSelect;
use App\Models\Color;
use App\Models\Cultivar;
use App\Models\Language;
use App\Models\Plant;
use App\Models\User;
use App\Support\NameDisplayPreferences;

beforeEach(function (): void {
    createRequiredLanguages();
    app()->setLocale('nl');
});

afterEach(function (): void {
    app(NameDisplayPreferences::class)->useUser(null);
});

it('inherits global choices and supports independent overrides', function (): void {
    $user = User::factory()->admin()->create([
        'name_display_preference' => NameDisplay::Scientific,
        'name_display_overrides' => ['cultivars' => 'translated'],
    ]);

    expect($user->nameDisplayFor(NameDisplayGroup::Plants))->toBe(NameDisplay::Scientific)
        ->and($user->nameDisplayFor(NameDisplayGroup::Cultivars))->toBe(NameDisplay::Translated);

    $user->name_display_preference = NameDisplay::Translated;
    $user->name_display_overrides = ['plants' => 'scientific'];
    expect($user->nameDisplayFor(NameDisplayGroup::Plants))->toBe(NameDisplay::Scientific)
        ->and($user->nameDisplayFor(NameDisplayGroup::Cultivars))->toBe(NameDisplay::Translated);
});

it('applies scientific defaults while allowing translated cultivar labels', function (): void {
    $dutch = Language::whereCode('nl')->firstOrFail();
    $plant = Plant::factory()->create(['name' => 'Prunus yedoensis']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => 'Akebono']);
    $plant->localizations()->create(['language_id' => $dutch->id, 'value' => 'Yoshino-kers']);
    $cultivar->localizations()->create(['language_id' => $dutch->id, 'value' => 'Sierkers']);
    $preferences = app(NameDisplayPreferences::class);
    $user = User::factory()->admin()->create([
        'name_display_preference' => NameDisplay::Scientific,
        'name_display_overrides' => ['cultivars' => 'translated'],
    ]);
    $preferences->useUser($user);

    expect($plant->display_name)->toBe('Prunus yedoensis')
        ->and($cultivar->display_name)->toBe('Sierkers')
        ->and($cultivar->scientific_name)->toBe("Prunus yedoensis 'Akebono'");

    $user->name_display_overrides = null;
    expect($cultivar->display_name)->toBe("Prunus yedoensis 'Akebono'");

    $preferences->useUser(null);
    expect($plant->display_name)->toBe('Yoshino-kers')
        ->and($cultivar->display_name)->toBe('Sierkers');
});

it('keeps colors translated regardless of botanical display preferences', function (): void {
    $dutch = Language::whereCode('nl')->firstOrFail();
    $color = Color::factory()->create(['name' => 'Red']);
    $color->localizations()->create(['language_id' => $dutch->id, 'value' => 'Rood']);
    app(NameDisplayPreferences::class)->useUser(User::factory()->admin()->create());

    expect($color->display_name)->toBe('Rood');
});

it('uses preferences consistently in plant and cultivar selectors', function (): void {
    $dutch = Language::whereCode('nl')->firstOrFail();
    $plant = Plant::factory()->create(['name' => 'Prunus yedoensis']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => 'Akebono']);
    $plant->localizations()->create(['language_id' => $dutch->id, 'value' => 'Yoshino-kers']);
    $cultivar->localizations()->create(['language_id' => $dutch->id, 'value' => 'Sierkers']);
    app(NameDisplayPreferences::class)->useUser(User::factory()->admin()->create([
        'name_display_preference' => NameDisplay::Scientific,
        'name_display_overrides' => ['cultivars' => 'translated'],
    ]));

    expect(PlantSelect::make('plant_id')->getSearchResults('Yoshino'))->toBe([$plant->id => 'Prunus yedoensis'])
        ->and(CultivarSelect::make('cultivar_id')->getSearchResults('Sierkers'))->toBe([$cultivar->id => 'Sierkers']);
});

it('handles unnamed cultivars and missing plants under either preference', function (): void {
    $user = User::factory()->admin()->create();
    app(NameDisplayPreferences::class)->useUser($user);
    $plant = Plant::factory()->create(['name' => 'Rosa rugosa']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => null]);

    expect($cultivar->display_name)->toBe('Rosa rugosa');
    $cultivar->plant_id = null;
    $cultivar->unsetRelation('parent');
    $unknown = __('Unknown :name', ['name' => Plant::getModelLabel()]);
    expect($cultivar->display_name)->toBe($unknown);

    $user->name_display_preference = NameDisplay::Translated;
    expect($cultivar->display_name)->toBe($unknown);
});
