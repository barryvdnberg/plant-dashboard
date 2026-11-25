<?php

namespace Database\Factories;

use App\Enums\PlantTrait;
use App\Models\PlantProperties;
use App\Models\PlantPropertiesTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantPropertiesTrait>
 */
class PlantPropertiesTraitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plant_properties_id' => PlantProperties::factory(),
            'trait_index' => $this->faker->randomElement(PlantTrait::cases()),
        ];
    }
}
