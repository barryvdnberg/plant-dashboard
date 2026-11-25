<?php

namespace Database\Factories;

use App\Enums\GrowthPattern;
use App\Models\PlantProperties;
use App\Models\PlantPropertiesGrowthStyle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantPropertiesGrowthStyle>
 */
class PlantPropertiesGrowthStyleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plant_properties_id' => PlantProperties::factory()->withoutAfterCreating(),
            'name' => $this->faker->randomElement(GrowthPattern::cases())->value,
        ];
    }
}
