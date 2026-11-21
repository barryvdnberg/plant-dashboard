<?php

use App\Models\Color;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Language;
use App\Models\Plant;
use App\Models\Species;

it('projects translated and fallback labels from a localized query', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $translated = Color::factory()->create(['name' => 'Red']);
    $fallback = Color::factory()->create(['name' => 'Green']);

    $translated->localizations()->create([
        'language_id' => $english->getKey(),
        'field' => 'name',
        'value' => 'Crimson',
    ]);

    $labels = Color::query()
        ->withLocalization(locale: 'en')
        ->addLocalizationCoalesce()
        ->pluck('_label', 'colors.id');

    expect($labels->all())->toMatchArray([
        $translated->getKey() => 'Crimson',
        $fallback->getKey() => 'Green',
    ]);
});

it('projects translated labels across a plant parent chain', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $family = Family::factory()->create();
    $genus = Genus::factory()->create(['family_id' => $family->getKey()]);
    $species = Species::factory()->create(['genus_id' => $genus->getKey()]);
    $plant = Plant::factory()->create(['species_id' => $species->getKey()]);

    $plant->localizations()->create([
        'language_id' => $english->getKey(),
        'field' => 'name',
        'value' => 'Translated plant',
    ]);
    $species->localizations()->create([
        'language_id' => $english->getKey(),
        'field' => 'name',
        'value' => 'Translated species',
    ]);
    $genus->localizations()->create([
        'language_id' => $english->getKey(),
        'field' => 'name',
        'value' => 'Translated genus',
    ]);

    $result = Plant::query()
        ->withLocalizedData(depth: 2, locale: 'en')
        ->addParentSelect()
        ->addLocalizationCoalesce()
        ->findOrFail($plant->getKey());

    expect($result->_label)->toBe('Translated plant')
        ->and($result->_parent_label)->toBe('Translated species')
        ->and($result->_parent_parent_label)->toBe('Translated genus');
});