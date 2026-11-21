<?php

use App\Filament\Resources\Cultivars\Pages\CreateCultivar;
use App\Filament\Resources\Cultivars\Pages\EditCultivar;
use App\Filament\Resources\Plants\Pages\CreatePlant;
use App\Models\Cultivar;
use App\Models\Language;
use App\Models\Plant;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
    createRequiredLanguages();
    app()->setLocale('en');
    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

it('creates a cultivar with translations through the panel form', function (): void {
    $plant = Plant::factory()->create(['name' => 'Prunus yedoensis']);
    $english = Language::whereCode('en')->firstOrFail();

    livewire(CreateCultivar::class)
        ->fillForm([
            'plant_id' => $plant->id,
            'name' => 'Akebono',
            'localizations' => [[
                'language_id' => $english->id,
                'field' => 'name',
                'value' => 'Akebono Yoshino cherry',
                'synonyms' => 'Spring blossom',
            ]],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $cultivar = Cultivar::whereName('Akebono')->firstOrFail();
    expect($cultivar->display_name)->toBe('Akebono Yoshino cherry')
        ->and($cultivar->localizations()->first()->synonyms)->toBe('Spring blossom');
});

it('updates and removes cultivar translations through the panel form', function (): void {
    $plant = Plant::factory()->create(['name' => 'Prunus yedoensis']);
    $cultivar = Cultivar::factory()->for($plant)->create(['name' => 'Akebono']);
    $english = Language::whereCode('en')->firstOrFail();
    $cultivar->localizations()->create(['language_id' => $english->id, 'value' => 'Old name']);

    livewire(EditCultivar::class, ['record' => $cultivar->id])
        ->fillForm(['localizations' => [[
            'language_id' => $english->id,
            'field' => 'name',
            'value' => 'New name',
        ]]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($cultivar->fresh()->display_name)->toBe('New name');

    livewire(EditCultivar::class, ['record' => $cultivar->id])
        ->fillForm(['localizations' => []])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($cultivar->fresh()->display_name)->toBe('Prunus yedoensis Akebono');
});

it('saves translations on the unnamed attribute holder inside the plant form', function (): void {
    $english = Language::whereCode('en')->firstOrFail();

    livewire(CreatePlant::class)
        ->fillForm([
            'name' => 'Rosa rugosa',
            'localizations' => [],
            'baseCultivar' => ['localizations' => [[
                'language_id' => $english->id,
                'field' => 'name',
                'value' => 'Rugosa rose',
            ]]],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $plant = Plant::whereName('Rosa rugosa')->firstOrFail();
    expect($plant->baseCultivar->name)->toBeNull()
        ->and($plant->baseCultivar->display_name)->toBe('Rugosa rose')
        ->and($plant->baseCultivar->scientific_name)->toBe('Rosa rugosa');
});
