<?php

namespace Database\Factories;

use App\Models\Cultivar;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cultivar>
 */
class CultivarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'name' => ucfirst($this->faker->words(2, true)),
            'image_paths' => [],
        ];
    }

    public function base(): static
    {
        return $this->state(['name' => null, 'is_default' => true]);
    }

    public function withDetails(): static
    {
        return $this
            ->has(PlantPropertiesFactory::new()
                ->has(PlantPropertiesGrowthStyleFactory::new(), 'growthStyles')
                ->has(PlantPropertiesTraitFactory::new(), 'traits'), 'properties')
            ->has(FoliageFactory::new(), 'foliage')
            ->has(PlantEnvironmentFactory::new(), 'environment')
            ->has(PlantMaintenanceFactory::new(), 'maintenance')
            ->has(PlantBloomingFactory::new(), 'blooming');
    }
}
