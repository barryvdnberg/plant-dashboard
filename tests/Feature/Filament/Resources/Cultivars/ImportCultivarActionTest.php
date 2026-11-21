<?php

use App\Enums\GrowthPattern;
use App\Enums\IntensityLevel;
use App\Enums\LeafRetention;
use App\Enums\PlantTrait;
use App\Enums\PlantType;
use App\Enums\SunExposure;
use App\Filament\Resources\Cultivars\Pages\CreateCultivar;
use App\Models\Color;
use App\Models\Cultivar;
use App\Models\GardenStyle;
use App\Models\Language;
use App\Models\Plant;
use App\Models\PlantMaintenance;
use App\Models\SoilType;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
    createRequiredLanguages();
    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
});

it('imports a cultivar into the create form and saves its related fields on a new record', function (): void {
    $plant = Plant::factory()->create();
    $english = Language::whereCode('en')->firstOrFail();
    $leafColor = Color::factory()->create();
    $springColor = Color::factory()->create();
    $style = GardenStyle::factory()->create();
    $soil = SoilType::factory()->create();
    $source = Cultivar::factory()->for($plant)->create([
        'name' => 'Source cultivar',
        'image_paths' => ['uploads/images/cultivars/source.jpg'],
    ]);
    $source->colors()->attach($springColor);
    $source->localizations()->create([
        'language_id' => $english->id,
        'field' => 'name',
        'value' => 'Source translation',
        'synonyms' => 'Source synonym',
    ]);

    $properties = $source->properties()->create(['plant_type' => PlantType::SHRUB->value]);
    $properties->growthStyles()->create(['name' => GrowthPattern::SHRUB->value]);
    $properties->traits()->create(['trait_index' => PlantTrait::HAS_SCENT->value]);
    $properties->gardenStyles()->attach($style);

    $foliage = $source->foliage()->create(['retention' => LeafRetention::EVERGREEN, 'seasonal_color_change' => true]);
    $foliage->colors()->attach($leafColor, ['tag' => 'default']);
    $foliage->colors()->attach($springColor, ['tag' => 'spring']);

    $environment = $source->environment()->create(['ph_range' => [5, 7]]);
    $environment->soilTypes()->attach($soil);
    $environment->sunExposures()->create(['value' => SunExposure::SUN->value]);

    $blooming = $source->blooming()->create();
    $blooming->months()->create(['index' => 4]);
    $blooming->colors()->attach($springColor);

    $maintenance = $source->maintenance()->create(['group' => GrowthPattern::DEFAULT, 'watering_needs' => IntensityLevel::LOW]);
    $recommendations = [0, 1, 2, ...array_fill(0, 9, 0)];
    foreach ($recommendations as $index => $value) {
        $maintenance->recommendations()->create(['position' => $index + 1, 'value' => $value]);
    }
    PlantMaintenance::factory()->for($source)->create([
        'group' => GrowthPattern::SHRUB,
        'watering_needs' => IntensityLevel::HIGH,
    ]);

    $page = livewire(CreateCultivar::class)
        ->fillForm(['plant_id' => $plant->id])
        ->mountAction(TestAction::make('importCultivar')->schemaComponent('form-actions', schema: 'content'))
        ->assertActionDataSet(['plant_id' => $plant->id])
        ->setActionData(['cultivar_id' => $source->id])
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertSchemaStateSet([
            'name' => 'Source cultivar',
            'image_paths' => [],
            'properties.plant_type' => PlantType::SHRUB->value,
        ], 'form');

    expect(Cultivar::count())->toBe(1);

    $page->fillForm(['name' => 'Imported cultivar'])
        ->call('create')
        ->assertHasNoFormErrors();

    $imported = Cultivar::whereName('Imported cultivar')->firstOrFail();
    expect($imported->plant_id)->toBe($plant->id)
        ->and($imported->image_paths)->toBe([])
        ->and($imported->colors->modelKeys())->toBe([$springColor->id])
        ->and($imported->localizations()->firstOrFail()->only(['language_id', 'field', 'value', 'synonyms']))
        ->toBe(['language_id' => $english->id, 'field' => 'name', 'value' => 'Source translation', 'synonyms' => 'Source synonym'])
        ->and($imported->properties->plant_type)->toBe(PlantType::SHRUB->value)
        ->and($imported->properties->growthStyles()->pluck('name')->all())->toBe([GrowthPattern::SHRUB->value])
        ->and($imported->properties->traits()->pluck('trait_index')->all())->toBe([PlantTrait::HAS_SCENT->value])
        ->and($imported->properties->gardenStyles->modelKeys())->toBe([$style->id])
        ->and($imported->foliage->retention)->toBe(LeafRetention::EVERGREEN->value)
        ->and($imported->foliage->defaultColors->modelKeys())->toBe([$leafColor->id])
        ->and($imported->foliage->springColors->modelKeys())->toBe([$springColor->id])
        ->and($imported->environment->ph_range)->toEqual([5, 7])
        ->and($imported->environment->soilTypes->modelKeys())->toBe([$soil->id])
        ->and($imported->environment->sunExposures()->pluck('value')->all())->toBe([SunExposure::SUN->value])
        ->and($imported->blooming->months()->pluck('index')->all())->toBe([4])
        ->and($imported->blooming->colors->modelKeys())->toBe([$springColor->id]);

    $importedMaintenance = PlantMaintenance::whereBelongsTo($imported)->get()->keyBy(fn (PlantMaintenance $row) => $row->group->value);
    expect($importedMaintenance)->toHaveCount(2)
        ->and($importedMaintenance['default']->recommendations()->orderBy('position')->pluck('value')->all())->toBe($recommendations)
        ->and($importedMaintenance['shrub']->watering_needs)->toBe(IntensityLevel::HIGH)
        ->and($source->fresh()->name)->toBe('Source cultivar');
});

it('rejects a source cultivar that does not belong to the selected plant', function (): void {
    $plant = Plant::factory()->create();
    $otherPlant = Plant::factory()->create();
    $source = Cultivar::factory()->for($otherPlant)->create();

    livewire(CreateCultivar::class)
        ->fillForm(['plant_id' => $plant->id, 'name' => 'Draft cultivar'])
        ->callAction(TestAction::make('importCultivar')->schemaComponent('form-actions', schema: 'content'), data: [
            'plant_id' => $plant->id,
            'cultivar_id' => $source->id,
        ])
        ->assertHasActionErrors(['cultivar_id'])
        ->assertSchemaStateSet(['name' => 'Draft cultivar', 'plant_id' => $plant->id], 'form');

    expect(Cultivar::count())->toBe(1);
});

it('can use the unnamed default cultivar as a source', function (): void {
    $plant = Plant::factory()->create();
    $source = Cultivar::factory()->for($plant)->create(['name' => null]);
    $source->forceFill(['is_default' => true])->save();
    $source->properties()->create(['plant_type' => PlantType::PERENNIAL->value]);

    livewire(CreateCultivar::class)
        ->fillForm(['plant_id' => $plant->id])
        ->callAction(TestAction::make('importCultivar')->schemaComponent('form-actions', schema: 'content'), data: [
            'cultivar_id' => $source->id,
        ])
        ->assertHasNoActionErrors()
        ->assertSchemaStateSet([
            'properties.plant_type' => PlantType::PERENNIAL->value,
        ], 'form')
        ->fillForm(['name' => 'Named cultivar'])
        ->call('create')
        ->assertHasNoFormErrors();

    $imported = Cultivar::whereName('Named cultivar')->firstOrFail();
    expect($imported->properties->plant_type)->toBe(PlantType::PERENNIAL->value)
        ->and($source->fresh()->name)->toBeNull();
});
