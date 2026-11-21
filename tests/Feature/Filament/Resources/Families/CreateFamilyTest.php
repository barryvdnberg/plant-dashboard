<?php

use App\Filament\Resources\Families\Pages\CreateFamily;
use App\Models\Family;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());

    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

it('creates a family', function (): void {
    createRequiredLanguages();

    $name = 'Test botanical family';

    livewire(CreateFamily::class)
        ->fillForm([
            'name' => $name,
            'image_paths' => [],
            'localizations' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Family::class, ['name' => $name]);
});