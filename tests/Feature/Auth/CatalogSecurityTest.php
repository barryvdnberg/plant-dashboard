<?php

use App\Filament\Resources\Families\Pages\ListFamilies;
use App\Filament\Resources\Plants\Pages\CreatePlant;
use App\Models\Family;
use App\Models\Plant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

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
