<?php

use App\Models\Color;
use App\Models\Language;
use App\Models\Localizable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Cache::flush();
});

it('returns translated color labels and falls back to the stored name', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $translated = Color::factory()->create(['name' => 'Red']);
    $fallback = Color::factory()->create(['name' => 'Green']);

    $translated->localizations()->create([
        'language_id' => $english->getKey(),
        'field' => 'name',
        'value' => 'Crimson',
    ]);

    expect(Color::localizedMap($english->getKey())->all())->toMatchArray([
        $translated->getKey() => 'Crimson',
        $fallback->getKey() => 'Green',
    ]);
});

it('invalidates cached color labels when a color changes', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $color = Color::factory()->create(['name' => 'Red']);

    expect(Color::localizedMap($english->getKey())->get($color->getKey()))->toBe('Red');

    $color->update(['name' => 'Crimson']);

    expect(Color::localizedMap($english->getKey())->get($color->getKey()))->toBe('Crimson');
});

it('uses the requested language instead of the current application locale', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $dutch = Language::factory()->create(['code' => 'nl']);
    $color = Color::factory()->create(['name' => 'Red']);

    $color->localizations()->createMany([
        [
            'language_id' => $english->getKey(),
            'field' => 'name',
            'value' => 'Red',
        ],
        [
            'language_id' => $dutch->getKey(),
            'field' => 'name',
            'value' => 'Rood',
        ],
    ]);

    app()->setLocale('en');

    expect(Color::localizedMap($dutch->getKey())->get($color->getKey()))->toBe('Rood');
});

it('deletes localizations when a localized model is deleted', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $color = Color::factory()->create();
    $localization = $color->localizations()->create([
        'language_id' => $english->getKey(),
        'field' => 'name',
        'value' => 'Translated color',
    ]);

    $color->delete();

    expect(Localizable::query()->find($localization->getKey()))->toBeNull();
});

it('reuses cached labels without querying the language again', function (): void {
    $language = Language::factory()->create(['code' => 'en']);
    Color::factory()->create(['name' => 'Red']);
    Color::localizedMap($language->id);

    DB::enableQueryLog();
    DB::flushQueryLog();

    try {
        Color::localizedMap($language->id);
        Color::localizedMap($language->id);
        expect(DB::getQueryLog())->toBeEmpty();
    } finally {
        DB::disableQueryLog();
    }
});

it('invalidates cached labels when a translation is created updated or deleted', function (): void {
    $language = Language::factory()->create(['code' => 'en']);
    $color = Color::factory()->create(['name' => 'Red']);
    expect(Color::localizedMap($language->id)->get($color->id))->toBe('Red');

    $translation = $color->localizations()->create([
        'language_id' => $language->id,
        'field' => 'name',
        'value' => 'Crimson',
    ]);
    expect(Color::localizedMap($language->id)->get($color->id))->toBe('Crimson');

    $translation = $color->localizations()->firstOrFail();
    $translation->update(['value' => 'Scarlet']);
    expect(Color::localizedMap($language->id)->get($color->id))->toBe('Scarlet');

    $translation->delete();
    expect(Color::localizedMap($language->id)->get($color->id))->toBe('Red');
});

it('invalidates both languages when a translation moves to another language', function (): void {
    createRequiredLanguages();
    $english = Language::whereCode('en')->firstOrFail();
    $dutch = Language::whereCode('nl')->firstOrFail();
    $color = Color::factory()->create(['name' => 'Red']);
    $translation = $color->localizations()->create(['language_id' => $english->id, 'value' => 'Crimson']);

    expect(Color::localizedMap($english->id)->get($color->id))->toBe('Crimson')
        ->and(Color::localizedMap($dutch->id)->get($color->id))->toBe('Red');

    $translation->update(['language_id' => $dutch->id, 'value' => 'Rood']);
    expect(Color::localizedMap($english->id)->get($color->id))->toBe('Red')
        ->and(Color::localizedMap($dutch->id)->get($color->id))->toBe('Rood');
});

it('does not let callers mutate the cached label map', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $color = Color::factory()->create(['name' => 'Red']);
    Color::localizedMap($english->id)->put($color->id, 'Changed by caller');

    expect(Color::localizedMap($english->id)->get($color->id))->toBe('Red');
});

it('invalidates cached labels when name translations are replaced', function (): void {
    createRequiredLanguages();
    $english = Language::whereCode('en')->firstOrFail();
    $dutch = Language::whereCode('nl')->firstOrFail();
    $color = Color::factory()->create(['name' => 'Red']);
    $color->localizations()->create(['language_id' => $dutch->id, 'value' => 'Rood']);
    expect(Color::localizedMap($dutch->id)->get($color->id))->toBe('Rood');

    $color->replaceNameTranslations(['en' => 'Crimson']);

    expect(Color::localizedMap($dutch->id)->get($color->id))->toBe('Red')
        ->and(Color::localizedMap($english->id)->get($color->id))->toBe('Crimson');
});
