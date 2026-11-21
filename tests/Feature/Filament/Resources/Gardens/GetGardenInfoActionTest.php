<?php

use App\Filament\Resources\Gardens\Pages\EditGarden;
use App\Models\Garden;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());

    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

it('loads garden information without halting the request', function (): void {
    $garden = Garden::query()->forceCreate([
        'user_id' => auth()->id(),
        'name' => 'Test garden',
        'winter_hardiness' => 1,
    ]);

    livewire(EditGarden::class, ['record' => $garden->getKey()])
        ->callAction(TestAction::make('getGardenInfo')->schemaComponent('form-actions', schema: 'content'))
        ->assertHasNoFormErrors();
});