<?php

use App\Filament\Resources\Gardens\Pages\CreateGarden;
use App\Filament\Resources\Gardens\Pages\EditGarden;
use App\Models\Garden;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

it('lets administrators create a garden for another user', function (): void {
    $owner = User::factory()->create();

    livewire(CreateGarden::class)
        ->fillForm(['name' => 'Managed garden', 'winter_hardiness' => 6, 'user_id' => $owner->id])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Garden::where('name', 'Managed garden')->sole()->user_id)->toBe($owner->id);
});

it('lets administrators edit another users garden without changing its owner', function (): void {
    $garden = Garden::factory()->create();

    livewire(EditGarden::class, ['record' => $garden->id])
        ->fillForm(['name' => 'Updated garden'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($garden->fresh()->name)->toBe('Updated garden')
        ->and($garden->fresh()->user_id)->toBe($garden->user_id);
});

it('lets administrators transfer garden ownership', function (): void {
    $garden = Garden::factory()->create();
    $newOwner = User::factory()->create();

    livewire(EditGarden::class, ['record' => $garden->id])
        ->fillForm(['user_id' => $newOwner->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($garden->fresh()->user_id)->toBe($newOwner->id);
});

it('rejects nonexistent garden owners', function (): void {
    $garden = Garden::factory()->create();

    livewire(EditGarden::class, ['record' => $garden->id])
        ->fillForm(['user_id' => 999999])
        ->call('save')
        ->assertHasFormErrors(['user_id']);

    expect($garden->fresh()->user_id)->toBe($garden->user_id);
});

it('separates editing an owned garden from assigning its owner', function (): void {
    expect(Gate::allows('assignOwner', Garden::class))->toBeTrue();

    $user = User::factory()->create();
    actingAs($user);
    $garden = Garden::factory()->for($user)->create();

    expect(Gate::allows('update', $garden))->toBeTrue()
        ->and(Gate::allows('assignOwner', Garden::class))->toBeFalse();
});
