<?php

use App\Enums\HardinessZone;
use App\Filament\Resources\Cultivars\Pages\EditCultivar;
use App\Models\Cultivar;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Language;
use App\Models\Localizable;
use App\Models\Plant;
use App\Models\Species;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('seeds a complete starter catalog and reuses its records on a second run', function (): void {
    $this->seed();

    expect(Family::withCount('genera')->orderBy('name')->pluck('genera_count', 'name')->all())
        ->toBe(['Asteraceae' => 2, 'Lamiaceae' => 2, 'Rosaceae' => 2])
        ->and(Genus::count())->toBe(6)
        ->and(Genus::withCount('species')->pluck('species_count')->unique()->all())->toBe([2])
        ->and(Species::count())->toBe(12)
        ->and(Plant::count())->toBe(12)
        ->and(Cultivar::whereNull('name')->where('is_default', true)->count())->toBe(12)
        ->and(Plant::has('baseCultivar')->count())->toBe(12)
        ->and(Cultivar::whereNotNull('name')->where('is_default', false)->count())->toBe(6);

    $strawberry = Plant::whereName('Fragaria × ananassa')->sole();
    expect($strawberry->species->name)->toBe('× ananassa')
        ->and($strawberry->getTranslation('nl'))->toBe('Tuinaardbei')
        ->and($strawberry->cultivars()->sole()->name)->toBe('Elsanta');

    $rose = Plant::whereName('Rosa rugosa')->sole()->cultivars()->whereName('Alba')->sole();
    expect($rose->environment->winter_hardiness)->toBe(HardinessZone::ZONE_3)
        ->and($rose->environment->ph_range)->toEqual([6, 8])
        ->and($rose->environment->soilTypes()->pluck('name')->all())->toBe(['Loam'])
        ->and($rose->blooming->months()->orderBy('index')->pluck('index')->all())->toBe([6, 7, 8])
        ->and($rose->blooming->colors()->pluck('name')->all())->toBe(['White']);

    $tables = [
        'languages', 'colors', 'garden_styles', 'soil_types', 'families', 'genera', 'species', 'plants',
        'cultivars', 'localizables', 'plant_properties', 'plant_properties_growth_styles',
        'plant_environments', 'plant_environment_sun_exposures', 'plant_environment_soil_type',
        'foliages', 'plant_bloomings', 'plant_blooming_months', 'colorables',
    ];
    $counts = array_map(fn (string $table): int => DB::table($table)->count(), $tables);
    $translationIds = Localizable::query()->orderBy('id')->pluck('id')->all();

    $this->seed();

    expect(array_map(fn (string $table): int => DB::table($table)->count(), $tables))->toBe($counts)
        ->and(Localizable::query()->orderBy('id')->pluck('id')->all())->toBe($translationIds);
});

it('fills existing starter records without overwriting name translations or seeding custom species', function (): void {
    $dutch = Language::factory()->create(['code' => 'nl']);
    $family = Family::factory()->create(['name' => 'Rosaceae']);
    $genus = Genus::factory()->for($family)->create(['name' => 'Fragaria']);
    $species = Species::factory()->for($genus)->create(['name' => '× ananassa']);
    $plant = Plant::factory()->for($species)->create(['name' => 'Fragaria × ananassa']);
    $plant->localizations()->create(['language_id' => $dutch->id, 'value' => 'My strawberry name']);
    $otherSpecies = Species::factory()->for($genus)->create(['name' => 'virginiana']);

    $this->seed();

    expect($genus->species()->count())->toBe(3)
        ->and($species->plants()->sole()->id)->toBe($plant->id)
        ->and($plant->fresh()->getTranslation('nl'))->toBe('My strawberry name')
        ->and($plant->fresh()->getTranslation('en'))->toBe('Garden strawberry')
        ->and($plant->baseCultivar()->sole()->is_default)->toBeTruthy()
        ->and($plant->cultivars()->sole()->name)->toBe('Elsanta')
        ->and($otherSpecies->plants()->exists())->toBeFalse();
});

it('only saves an environment when it has a non-default pH range or other details', function (): void {
    createRequiredLanguages();
    $cultivar = Cultivar::factory()->create();

    actingAs(User::factory()->admin()->create());
    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();

    $page = livewire(EditCultivar::class, ['record' => $cultivar->getKey()])
        ->assertSchemaStateSet(['environment.ph_range' => [6.0, 8.0]], 'form')
        ->call('save')
        ->assertHasNoFormErrors();

    expect($cultivar->environment()->exists())->toBeFalse();

    $page->fillForm(['environment.ph_range' => [5.5, 7.5]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($cultivar->environment()->sole()->ph_range)->toEqual([5.5, 7.5]);

    $page->fillForm(['environment.ph_range' => [6, 8], 'environment.winter_hardiness' => HardinessZone::ZONE_3->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($cultivar->environment()->sole()->ph_range)->toEqual([6, 8])
        ->and($cultivar->environment()->sole()->winter_hardiness)->toBe(HardinessZone::ZONE_3);
});
