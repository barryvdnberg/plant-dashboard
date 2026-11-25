<?php

use App\Enums\GrowthPattern;
use App\Enums\HardinessZone;
use App\Enums\PlantTrait;
use App\Enums\Season;
use App\Enums\SunExposure;
use App\Models\Cultivar;
use App\Models\Foliage;
use App\Models\GardenPlant;
use App\Models\Plant;
use App\Models\PlantProperties;
use App\Models\PlantPropertiesGrowthStyle;
use App\Models\PlantPropertiesTrait;
use Faker\Generator;

it('builds a complete synthetic cultivar with consistent taxonomy and related details', function (): void {
    $plant = Plant::factory()->create();
    $base = Cultivar::factory()->for($plant)->base()->withDetails()->create();

    expect($plant->species->genus->family)->not->toBeNull()
        ->and($plant->name)->toBe($plant->species->scientific_name)
        ->and($plant->baseCultivar()->sole()->id)->toBe($base->id)
        ->and($base->is_default)->toBeTrue()
        ->and($base->properties->growthStyles()->sole()->name)->toBeIn(array_column(GrowthPattern::cases(), 'value'))
        ->and($base->properties->traits()->sole()->trait_index)->toBeInstanceOf(PlantTrait::class)
        ->and($base->foliage->cultivar_id)->toBe($base->id)
        ->and($base->environment->winter_hardiness)->toBeInstanceOf(HardinessZone::class)
        ->and($base->maintenance->group)->toBe(GrowthPattern::DEFAULT)
        ->and(Cultivar::count())->toBe(1)
        ->and(Plant::count())->toBe(1);

    $exposures = $base->environment->sunExposures()->pluck('value');
    $months = $base->blooming->months()->pluck('index');
    expect($exposures)->not->toBeEmpty()
        ->and($exposures->diff(array_column(SunExposure::cases(), 'value')))->toBeEmpty()
        ->and($exposures->unique())->toHaveCount($exposures->count())
        ->and($months)->not->toBeEmpty()
        ->and($months->diff(range(1, 12)))->toBeEmpty()
        ->and($months->unique())->toHaveCount($months->count());
});

it('creates standalone growth styles, traits and garden plants from an empty database', function (): void {
    $growthStyle = PlantPropertiesGrowthStyle::factory()->create();
    $trait = PlantPropertiesTrait::factory()->create();
    $gardenPlant = GardenPlant::factory()->create();

    expect($growthStyle->plantProperties->cultivar)->not->toBeNull()
        ->and($growthStyle->plantProperties->growthStyles()->count())->toBe(1)
        ->and($trait->plantProperties->cultivar)->not->toBeNull()
        ->and($growthStyle->plant_properties_id)->not->toBe($trait->plant_properties_id)
        ->and($gardenPlant->garden->user)->not->toBeNull()
        ->and($gardenPlant->cultivar->plant)->not->toBeNull();
});

it('optionally creates a growth style for plant properties', function (bool $shouldCreate): void {
    $faker = Mockery::mock($this->app->make(Generator::class));
    $faker->shouldReceive('boolean')->andReturn($shouldCreate);
    $this->app->instance(Generator::class, $faker);

    $properties = PlantProperties::factory()->create();
    $withExplicitStyle = PlantProperties::factory()
        ->has(PlantPropertiesGrowthStyle::factory(), 'growthStyles')
        ->create();

    expect($properties->growthStyles()->count())->toBe((int) $shouldCreate)
        ->and($withExplicitStyle->growthStyles()->count())->toBe(1);
})->with([true, false]);

it('generates tagged foliage colors according to seasonal color change', function (bool $seasonal): void {
    $foliage = Foliage::factory()->create(['seasonal_color_change' => $seasonal]);
    $counts = $foliage->colors->countBy('pivot.tag');
    $tags = $seasonal ? ['default', ...array_column(Season::cases(), 'value')] : ['default'];

    expect($counts->keys()->all())->toEqualCanonicalizing($tags);

    foreach ($counts as $count) {
        expect($count)->toBeBetween(1, 3);
    }
})->with([true, false]);
