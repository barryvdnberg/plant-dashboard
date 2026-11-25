<?php

use App\Filament\Resources\Plants\Pages\CreatePlant;
use App\Models\Plant;
use App\Models\Species;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());

    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

it('creates a plant', function (): void {
    createRequiredLanguages();

    $species = Species::factory()->create();
    $name = 'Test botanical plant';

    livewire(CreatePlant::class)
        ->fillForm([
            'species_id' => $species->getKey(),
            'name' => $name,
            'image_paths' => [],
            'localizations' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Plant::class, [
        'species_id' => $species->getKey(),
        'name' => $name,
    ]);
});

it('requires a plant name', function (): void {
    createRequiredLanguages();

    livewire(CreatePlant::class)
        ->fillForm([
            'name' => null,
            'image_paths' => [],
            'localizations' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('generates the plant name from the selected species', function (): void {
    createRequiredLanguages();
    $species = Species::factory()->create(['genus_id' => null, 'name' => 'officinalis']);

    livewire(CreatePlant::class)
        ->fillForm(['species_id' => $species->getKey()])
        ->callAction(TestAction::make('generate')->schemaComponent('name'))
        ->assertSchemaStateSet(['name' => $species->name]);
});
