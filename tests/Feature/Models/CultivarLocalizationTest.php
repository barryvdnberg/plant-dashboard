<?php

use App\Filament\Forms\Components\ModelSelects\CultivarSelect;
use App\Models\Cultivar;
use App\Models\Genus;
use App\Models\Language;
use App\Models\Plant;
use App\Models\Species;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    createRequiredLanguages();
    app()->setLocale('en');
});

it('separates scientific translated and display cultivar names', function (): void {
    $plant = Plant::factory()->create(['name' => 'Prunus yedoensis']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => 'Akebono']);
    $plant->localizations()->create(['language_id' => Language::whereCode('nl')->value('id'), 'value' => 'Yoshino-kers']);
    $cultivar->localizations()->createMany([
        ['language_id' => Language::whereCode('en')->value('id'), 'value' => 'Akebono Yoshino cherry'],
        ['language_id' => Language::whereCode('nl')->value('id'), 'value' => 'sierkers', 'synonyms' => 'Yoshino-kers'],
    ]);

    expect($cultivar->scientific_name)->toBe("Prunus yedoensis 'Akebono'")
        ->and($cultivar->display_name)->toBe('Akebono Yoshino cherry')
        ->and($cultivar->getTranslatedName('nl'))->toBe('Yoshino-kers sierkers')
        ->and($cultivar->getTranslatedName('nl', depth: 0))->toBe('sierkers');

    app()->setLocale('nl');
    expect($cultivar->display_name)->toBe('sierkers')
        ->and($cultivar->scientific_name)->toBe("Prunus yedoensis 'Akebono'");
});

it('falls back independently for each requested parent generation', function (): void {
    $genus = Genus::factory()->create(['name' => 'Prunus']);
    $species = Species::factory()->for($genus)->create(['name' => 'yedoensis']);
    $plant = Plant::factory()->for($species)->create(['name' => 'Prunus yedoensis']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => 'Akebono']);
    $plant->localizations()->create(['language_id' => Language::whereCode('nl')->value('id'), 'value' => 'Yoshino-kers']);

    expect($cultivar->getTranslatedName('nl'))->toBe('Yoshino-kers Akebono')
        ->and($cultivar->getTranslatedName('nl', 2))->toBe('yedoensis Yoshino-kers Akebono')
        ->and($cultivar->getTranslatedName('en'))->toBe('Prunus yedoensis Akebono')
        ->and($plant->getTranslatedName('nl'))->toBe('Yoshino-kers')
        ->and($plant->scientific_name)->toBe('Prunus yedoensis')
        ->and($species->scientific_name)->toBe('Prunus yedoensis');
});

it('uses the plant name for an unnamed default cultivar', function (?string $name): void {
    $plant = Plant::factory()->create(['name' => 'Rosa rugosa']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => $name]);

    expect($cultivar->fresh()->name)->toBeNull()
        ->and($plant->baseCultivar->id)->toBe($cultivar->id)
        ->and($plant->cultivars()->exists())->toBeFalse()
        ->and($cultivar->display_name)->toBe('Rosa rugosa')
        ->and($cultivar->scientific_name)->toBe('Rosa rugosa');

    $plant->localizations()->create(['language_id' => Language::whereCode('en')->value('id'), 'value' => 'Rugosa rose']);
    expect($cultivar->display_name)->toBe('Rugosa rose');

    $cultivar->localizations()->create(['language_id' => Language::whereCode('en')->value('id'), 'value' => 'Default rose']);
    expect($cultivar->display_name)->toBe('Default rose');
})->with([null, '']);

it('handles a missing plant with an unknown placeholder', function (): void {
    $cultivar = Cultivar::factory()->create(['name' => 'Akebono', 'plant_id' => null]);
    $unknown = __('Unknown :name', ['name' => Plant::getModelLabel()]);

    expect($cultivar->display_name)->toBe($unknown.' Akebono')
        ->and($cultivar->scientific_name)->toBe($unknown." 'Akebono'");

    $cultivar->name = null;
    expect($cultivar->display_name)->toBe($unknown)
        ->and($cultivar->scientific_name)->toBe($unknown);

    $cultivar->localizations()->create(['language_id' => Language::whereCode('en')->value('id'), 'value' => 'Known common name']);
    expect($cultivar->display_name)->toBe('Known common name');
});

it('resolves loaded cultivar names without per-row queries and allows locale changes', function (): void {
    $plant = Plant::factory()->create(['name' => 'Prunus yedoensis']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => 'Akebono']);
    $cultivar->localizations()->createMany([
        ['language_id' => Language::whereCode('en')->value('id'), 'value' => 'English cultivar'],
        ['language_id' => Language::whereCode('nl')->value('id'), 'value' => 'Dutch cultivar'],
    ]);
    $records = Cultivar::query()->withLocalizedData()->addLocalizationSelect()->get();
    DB::enableQueryLog();
    DB::flushQueryLog();

    try {
        expect($records->first()->display_name)->toBe('English cultivar');
        app()->setLocale('nl');
        expect($records->first()->display_name)->toBe('Dutch cultivar')
            ->and($records->first()->scientific_name)->toBe("Prunus yedoensis 'Akebono'")
            ->and(DB::getQueryLog())->toBeEmpty();
    } finally {
        DB::disableQueryLog();
    }
});

it('searches cultivar translations and synonyms with the same label as the model', function (): void {
    $plant = Plant::factory()->create(['name' => 'Prunus yedoensis']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => 'Akebono']);
    $cultivar->localizations()->create(['language_id' => Language::whereCode('en')->value('id'), 'value' => 'Akebono Yoshino cherry', 'synonyms' => 'Spring blossom']);
    $select = CultivarSelect::make('cultivar_id');

    expect($select->getSearchResults('Spring blossom'))->toBe([$cultivar->id => $cultivar->display_name])
        ->and($select->getSearchResults('Yoshino cherry'))->toBe([$cultivar->id => $cultivar->display_name]);
});

it('copies translations when replicating a cultivar and deletes them with the cultivar', function (): void {
    $cultivar = Cultivar::factory()->create();
    $cultivar->localizations()->create(['language_id' => Language::whereCode('en')->value('id'), 'value' => 'Common name']);
    $copy = $cultivar->replicate();
    $copy->save();
    $cultivar->replicateRelationsTo($copy);
    expect($copy->display_name)->toBe('Common name');

    $cultivar->delete();
    expect($cultivar->localizations()->count())->toBe(0)
        ->and($copy->localizations()->count())->toBe(1);
});
