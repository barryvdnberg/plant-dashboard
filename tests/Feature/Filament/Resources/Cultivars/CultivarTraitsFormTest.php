<?php

use App\Enums\PlantTrait;
use App\Enums\PlantType;
use App\Filament\Forms\Components\ColoredCheckboxList;
use App\Filament\Resources\Cultivars\Pages\CreateCultivar;
use App\Filament\Resources\Cultivars\Pages\EditCultivar;
use App\Models\Cultivar;
use App\Models\GardenStyle;
use App\Models\Plant;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
    createRequiredLanguages();
    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

it('creates plant properties from trait selections alone', function (): void {
    $plant = Plant::factory()->create();

    livewire(CreateCultivar::class)
        ->fillForm([
            'plant_id' => $plant->id,
            'name' => 'Scented cultivar',
            'properties.plant_traits' => [PlantTrait::HAS_SCENT->value],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $cultivar = Cultivar::whereName('Scented cultivar')->firstOrFail();
    expect($cultivar->properties->traits()->sole()->trait_index)->toBe(PlantTrait::HAS_SCENT);
});

it('loads and updates trait rows while preserving existing selections', function (): void {
    $cultivar = Cultivar::factory()->for(Plant::factory())->create();
    $properties = $cultivar->properties()->create(['plant_type' => PlantType::SHRUB->value]);
    $retained = $properties->traits()->create(['trait_index' => PlantTrait::HAS_SCENT]);
    $removed = $properties->traits()->create(['trait_index' => PlantTrait::TOXIC_TO_PETS]);

    livewire(EditCultivar::class, ['record' => $cultivar->id])
        ->assertSchemaStateSet([
            'properties.plant_traits' => [
                (string) PlantTrait::HAS_SCENT->value,
                (string) PlantTrait::TOXIC_TO_PETS->value,
            ],
        ], 'form')
        ->fillForm(['properties.plant_traits' => [
            PlantTrait::HAS_SCENT->value,
            PlantTrait::ATTRACTS_INSECTS->value,
            PlantTrait::ATTRACTS_INSECTS->value,
        ]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($properties->traits()->pluck('trait_index')->all())
        ->toEqualCanonicalizing([PlantTrait::HAS_SCENT, PlantTrait::ATTRACTS_INSECTS])
        ->and($retained->fresh())->not->toBeNull()
        ->and($removed->fresh())->toBeNull();

    livewire(EditCultivar::class, ['record' => $cultivar->id])
        ->set('data.properties.plant_traits', ['999'])
        ->call('save')
        ->assertHasFormErrors(['properties.plant_traits.0']);

    expect($properties->traits()->count())->toBe(2);

    livewire(EditCultivar::class, ['record' => $cultivar->id])
        ->fillForm(['properties.plant_traits' => []])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($properties->traits()->count())->toBe(0);
});

it('keeps Filament’s many-to-many options and save behavior with relationship()', function (): void {
    $cultivar = Cultivar::factory()->for(Plant::factory())->create();
    $properties = $cultivar->properties()->create(['plant_type' => PlantType::SHRUB->value]);
    $oldStyle = GardenStyle::factory()->create(['name' => 'Formal']);
    $newStyle = GardenStyle::factory()->create(['name' => 'Natural']);
    $excludedStyle = GardenStyle::factory()->create(['name' => 'Excluded']);
    $properties->gardenStyles()->attach([$oldStyle->id, $excludedStyle->id]);

    $page = livewire(EditCultivar::class, ['record' => $cultivar->id]);
    $list = ColoredCheckboxList::make('garden_styles')->relationship(
        name: fn (): string => 'gardenStyles',
        titleAttribute: 'name',
        modifyQueryUsing: fn (Builder $query): Builder => $query->whereKeyNot($excludedStyle->id),
    );
    $schema = Schema::make($page->instance())
        ->model($properties)
        ->statePath('data.properties')
        ->components([$list])
        ->fill([]);

    expect($list->getOptions())->toBe([$oldStyle->id => 'Formal', $newStyle->id => 'Natural'])
        ->and($schema->getRawState()['garden_styles'])->toBe([(string) $oldStyle->id]);

    $schema->rawState(['garden_styles' => [(string) $newStyle->id]])->saveRelationships();

    expect($properties->gardenStyles()->pluck('garden_styles.id')->all())
        ->toEqualCanonicalizing([$newStyle->id, $excludedStyle->id]);
});
