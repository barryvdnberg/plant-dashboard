<?php

use App\Models\Color;
use App\Models\Cultivar;
use App\Models\Language;
use App\Models\Plant;
use App\Models\SoilType;
use App\Models\Species;
use Database\Seeders\ColorSeeder;
use Database\Seeders\GardenStyleSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SoilTypeSeeder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\Fixtures\Models\LocalizedPlantWithProperties;

it('uses name fields before legacy translations and ignores unrelated and blank fields', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $color = Color::factory()->create(['name' => 'Red']);
    $legacy = $color->localizations()->create(['language_id' => $english->id, 'value' => 'Legacy red']);
    $color->localizations()->create(['language_id' => $english->id, 'field' => 'description', 'value' => 'A description']);
    $name = $color->localizations()->create(['language_id' => $english->id, 'field' => 'name', 'value' => 'Crimson']);

    $labels = fn () => Color::query()->withLocalization(locale: 'en')->addLocalizationCoalesce()->get();
    expect($color->getTranslation('en'))->toBe('Crimson')
        ->and($labels())->toHaveCount(1)
        ->and($labels()->first()->_label)->toBe('Crimson');

    $name->update(['value' => '   ']);
    expect($color->getTranslatedName('en'))->toBe('Legacy red')
        ->and($labels()->first()->_label)->toBe('Legacy red');

    $legacy->delete();
    expect($color->getTranslatedName('en'))->toBe('Red')
        ->and($labels()->first()->_label)->toBe('Red');
});

it('supports morph aliases in localization joins and cache invalidation', function (): void {
    $oldMap = Relation::morphMap();
    Relation::morphMap(['color' => Color::class]);

    try {
        $english = Language::factory()->create(['code' => 'en']);
        $color = Color::factory()->create(['name' => 'Red']);
        expect(Color::localizedMap($english->id)->get($color->id))->toBe('Red');
        $color->localizations()->create(['language_id' => $english->id, 'value' => 'Crimson']);
        expect(Color::localizedMap($english->id)->get($color->id))->toBe('Crimson');
    } finally {
        Relation::morphMap($oldMap, false);
    }
});

it('reuses localization and parent joins when applied more than once', function (): void {
    Language::factory()->create(['code' => 'en']);
    $plant = Plant::factory()->create(['name' => 'Rosa rugosa']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => null]);

    $query = Cultivar::query()->withLocalizedData(depth: 1)->withLocalizedData(depth: 5)->addLocalizationCoalesce();
    expect($query->findOrFail($cultivar->id)->display_name)->toBe('Rosa rugosa');
});

it('falls back to stored names for an unregistered locale instead of translating to English', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $color = Color::factory()->create(['name' => 'Red']);
    $color->localizations()->create(['language_id' => $english->id, 'value' => 'Crimson']);
    app()->setLocale('fr');

    expect($color->translated_name)->toBe('Red')
        ->and(Color::query()->withLocalization(locale: 'fr')->addLocalizationCoalesce()->first()->_label)->toBe('Red')
        ->and(Color::localizedMap()->get($color->id))->toBe('Red');
});

it('composes self localization with configured belongs-to properties without trait conflicts', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $species = Species::factory()->create(['name' => 'rugosa']);
    $species->localizations()->create(['language_id' => $english->id, 'value' => 'Rugosa rose']);
    $plant = Plant::factory()->for($species)->create(['name' => 'Rosa rugosa']);

    $result = LocalizedPlantWithProperties::query()
        ->withLocalizedData(depth: 0, locale: 'en')
        ->withPropertyLocalization('species', locale: 'en')
        ->addConfiguredBelongsToPropertySelect()
        ->addLocalizationCoalesce()
        ->findOrFail($plant->id);

    expect($result->_label)->toBe('Rosa rugosa')
        ->and($result->_species_label)->toBe('Rugosa rose')
        ->and($result->_species_name)->toBe('rugosa');
});

it('preserves other translated fields when replacing names', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $color = Color::factory()->create(['name' => 'Red']);
    $color->localizations()->create(['language_id' => $english->id, 'field' => 'description', 'value' => 'Description']);
    $color->replaceNameTranslations(['en' => 'Crimson']);

    expect($color->getTranslation('en', field: 'description'))->toBe('Description')
        ->and($color->getTranslation('en'))->toBe('Crimson');
});

it('uses the shared name translation writer throughout the catalog seeders', function (): void {
    $this->seed([
        LanguageSeeder::class,
        ColorSeeder::class,
        SoilTypeSeeder::class,
        GardenStyleSeeder::class,
    ]);

    expect(Color::whereName('Red')->firstOrFail()->getTranslation('nl'))->toBe('Rood')
        ->and(SoilType::whereName('Loam')->firstOrFail()->getTranslation('nl'))->toBe('Leem')
        ->and(Language::whereCode('en')->firstOrFail()->getTranslation('nl'))->toBe('Engels');
});
