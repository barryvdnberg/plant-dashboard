<?php

use App\Filament\App\Resources\Gardens\Pages\CreateGarden;
use App\Filament\App\Resources\Gardens\Pages\EditGarden;
use App\Filament\Resources\Gardens\RelationManagers\PlantsRelationManager;
use App\Models\Cultivar;
use App\Models\Garden;
use App\Models\GardenPlant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs($this->user = User::factory()->create());
    Filament::setCurrentPanel('app');
    Filament::bootCurrentPanel();
});

it('assigns garden ownership on the server despite a forged owner', function (): void {
    $victim = User::factory()->create();

    livewire(CreateGarden::class)
        ->fillForm(['name' => 'My garden', 'winter_hardiness' => 6])
        ->set('data.user_id', $victim->id)
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Garden::where('name', 'My garden')->sole()->user_id)->toBe($this->user->id);
});

it('does not transfer ownership through a forged edit', function (): void {
    $garden = Garden::factory()->for($this->user)->create();
    $victim = User::factory()->create();

    livewire(EditGarden::class, ['record' => $garden->id])
        ->fillForm(['name' => 'Updated garden'])
        ->set('data.user_id', $victim->id)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($garden->fresh()->user_id)->toBe($this->user->id);
});

it('denies another users garden routes', function (string $suffix): void {
    $garden = Garden::factory()->create();

    $this->get('/app/gardens/'.$garden->id.$suffix)->assertNotFound();
})->with(['', '/edit', '/plants', '/actions']);

it('authorizes garden and plant records by ownership', function (): void {
    $own = Garden::factory()->for($this->user)->create();
    $other = Garden::factory()->create();
    $plant = $other->plants()->create(['cultivar_id' => Cultivar::factory()->create()->id, 'name' => 'Private plant']);

    expect(Gate::allows('update', $own))->toBeTrue()
        ->and(Gate::allows('update', $other))->toBeFalse()
        ->and(Gate::allows('delete', $plant))->toBeFalse();

    actingAs(User::factory()->admin()->create());
    expect(Gate::allows('update', $other))->toBeTrue()
        ->and(Gate::allows('delete', $plant))->toBeTrue();
});

it('requires ownership of both gardens when moving a garden plant', function (): void {
    $own = Garden::factory()->for($this->user)->create();
    $other = Garden::factory()->create();
    $ownPlant = GardenPlant::factory()->for($own)->create();
    $otherPlant = GardenPlant::factory()->for($other)->create();

    expect(Gate::allows('createIn', [GardenPlant::class, $own]))->toBeTrue()
        ->and(Gate::allows('createIn', [GardenPlant::class, $other]))->toBeFalse()
        ->and(Gate::allows('moveTo', [$ownPlant, $own]))->toBeTrue()
        ->and(Gate::allows('moveTo', [$ownPlant, $other]))->toBeFalse()
        ->and(Gate::allows('moveTo', [$otherPlant, $own]))->toBeFalse();
});

it('cannot mount another users plant manager directly', function (): void {
    livewire(PlantsRelationManager::class, [
        'ownerRecord' => Garden::factory()->create(),
        'pageClass' => EditGarden::class,
    ])->assertForbidden();
});

it('cannot associate another users plant with a forged record id', function (): void {
    $own = Garden::factory()->for($this->user)->create();
    $other = Garden::factory()->create();
    $plant = $other->plants()->create(['cultivar_id' => Cultivar::factory()->create()->id, 'name' => 'Private plant']);

    livewire(PlantsRelationManager::class, ['ownerRecord' => $own, 'pageClass' => EditGarden::class])
        ->callTableAction('associate', data: ['recordId' => $plant->id]);

    expect($plant->fresh()->garden_id)->toBe($other->id);
});

it('allows moving a plant between owned gardens', function (): void {
    $source = Garden::factory()->for($this->user)->create();
    $destination = Garden::factory()->for($this->user)->create();
    $plant = $source->plants()->create(['cultivar_id' => Cultivar::factory()->create()->id, 'name' => 'My plant']);

    livewire(PlantsRelationManager::class, ['ownerRecord' => $destination, 'pageClass' => EditGarden::class])
        ->callTableAction('associate', data: ['recordId' => $plant->id])
        ->assertHasNoTableActionErrors();

    expect($plant->fresh()->garden_id)->toBe($destination->id);
});

it('does not mass assign administrator privileges', function (): void {
    $this->user->fill(['is_admin' => true])->save();
    expect($this->user->fresh()->is_admin)->toBeFalse();
});

it('denies catalog writes to regular users', function (): void {
    $cultivar = Cultivar::factory()->create();
    expect(Gate::allows('view', $cultivar))->toBeTrue()
        ->and(Gate::allows('create', Cultivar::class))->toBeFalse()
        ->and(Gate::allows('update', $cultivar))->toBeFalse()
        ->and(Gate::allows('delete', $cultivar))->toBeFalse();
});

it('rechecks ownership on subsequent plant manager requests', function (): void {
    $garden = Garden::factory()->for($this->user)->create();
    $component = livewire(PlantsRelationManager::class, ['ownerRecord' => $garden, 'pageClass' => EditGarden::class]);
    $garden->update(['user_id' => User::factory()->create()->id]);

    $component->call('$refresh')->assertForbidden();
});

it('rejects invalid garden hardiness values', function (): void {
    livewire(CreateGarden::class)
        ->fillForm(['name' => 'My garden', 'winter_hardiness' => 99])
        ->call('create')
        ->assertHasFormErrors(['winter_hardiness']);

    expect(Garden::count())->toBe(0);
});

it('creates garden plants with validated input', function (): void {
    $garden = Garden::factory()->for($this->user)->create();
    $cultivar = Cultivar::factory()->create();

    livewire(PlantsRelationManager::class, ['ownerRecord' => $garden, 'pageClass' => EditGarden::class])
        ->callTableAction('create', data: ['cultivar_id' => $cultivar->id, 'name' => 'My plant', 'size' => 20])
        ->assertHasNoTableActionErrors();

    expect($garden->plants()->sole()->name)->toBe('My plant');
});

it('rejects invalid garden plant sizes', function (int $size): void {
    $garden = Garden::factory()->for($this->user)->create();
    $cultivar = Cultivar::factory()->create();

    livewire(PlantsRelationManager::class, ['ownerRecord' => $garden, 'pageClass' => EditGarden::class])
        ->callTableAction('create', data: ['cultivar_id' => $cultivar->id, 'name' => 'My plant', 'size' => $size])
        ->assertHasTableActionErrors(['size']);

    expect($garden->plants()->count())->toBe(0);
})->with([-1, 1000000]);
