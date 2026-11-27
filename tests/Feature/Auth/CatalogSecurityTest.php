<?php

use App\Filament\Resources\Families\Pages\ListFamilies;
use App\Filament\Resources\Genera\Pages\ListGenera;
use App\Filament\Resources\Plants\Pages\CreatePlant;
use App\Filament\Resources\Plants\Pages\EditPlant;
use App\Filament\Resources\Plants\RelationManagers\CultivarsRelationManager;
use App\Filament\Resources\Species\Pages\ListSpecies;
use App\Models\Color;
use App\Models\Cultivar;
use App\Models\Family;
use App\Models\GardenStyle;
use App\Models\Genus;
use App\Models\Language;
use App\Models\Localizable;
use App\Models\Plant;
use App\Models\SoilType;
use App\Models\Species;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

it('discovers a dedicated policy for each catalog model', function (string $model): void {
    $record = new $model;
    $member = Gate::forUser(User::factory()->make());
    $policy = 'App\\Policies\\'.class_basename($model).'Policy';

    expect(Gate::getPolicyFor($model))->toBeInstanceOf($policy)
        ->and(Gate::allows('update', $record))->toBeTrue()
        ->and($member->allows('view', $record))->toBeTrue()
        ->and($member->allows('update', $record))->toBeFalse();
})->with([
    Color::class,
    Cultivar::class,
    Family::class,
    GardenStyle::class,
    Genus::class,
    Language::class,
    Localizable::class,
    Plant::class,
    SoilType::class,
    Species::class,
]);

it('validates inline names before persisting', function (string $name): void {
    $family = Family::factory()->create(['name' => 'Original']);

    livewire(ListFamilies::class)->call('updateTableColumnState', 'name', (string) $family->id, $name);

    expect($family->fresh()->name)->toBe('Original');
})->with(['', str_repeat('x', 101)]);

it('saves valid inline names', function (): void {
    $family = Family::factory()->create(['name' => 'Original']);

    livewire(ListFamilies::class)->call('updateTableColumnState', 'name', (string) $family->id, 'Updated');

    expect($family->fresh()->name)->toBe('Updated');
});

it('denies inline writes after admin privileges are revoked', function (): void {
    $family = Family::factory()->create(['name' => 'Original']);
    $component = livewire(ListFamilies::class);
    auth()->user()->forceFill(['is_admin' => false])->save();

    $component->call('updateTableColumnState', 'name', (string) $family->id, 'Tampered');

    expect($family->fresh()->name)->toBe('Original');
});

it('requires catalog import permission', function (string $page): void {
    livewire($page)->assertTableActionVisible('import');

    actingAs(User::factory()->create());
    livewire($page)->assertTableActionHidden('import');
})->with([ListFamilies::class, ListGenera::class, ListSpecies::class]);

it('requires catalog update permission to move a cultivar to another plant', function (): void {
    $cultivar = Cultivar::factory()->create();
    $destination = Plant::factory()->create();
    $parameters = ['ownerRecord' => $destination, 'pageClass' => EditPlant::class];
    $data = ['plant_id' => $cultivar->plant_id, 'recordId' => $cultivar->id];

    actingAs(User::factory()->create());
    livewire(CultivarsRelationManager::class, $parameters)
        ->assertTableActionHidden('associate');

    actingAs(User::factory()->admin()->create());
    livewire(CultivarsRelationManager::class, $parameters)
        ->callTableAction('associate', data: $data)
        ->assertHasNoTableActionErrors();

    expect($cultivar->fresh()->plant_id)->toBe($destination->id);
});

it('rejects substituting an existing file path into a new upload field', function (): void {
    createRequiredLanguages();
    Storage::fake('local');
    Storage::fake('public');
    foreach (['local', 'public'] as $disk) {
        Storage::disk($disk)->put('uploads/images/unowned.png', 'private content');
    }

    livewire(CreatePlant::class)
        ->fillForm(['name' => 'Injected image', 'localizations' => []])
        ->set('data.image_paths', ['forged' => 'uploads/images/unowned.png'])
        ->call('create')
        ->assertHasFormErrors(['image_paths']);

    expect(Plant::where('name', 'Injected image')->exists())->toBeFalse();
});
