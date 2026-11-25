<?php

namespace Database\Factories;

use App\Enums\PlantType;
use App\Models\Cultivar;
use App\Models\PlantProperties;
use App\Models\PlantPropertiesGrowthStyle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantProperties>
 */
class PlantPropertiesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cultivar_id' => Cultivar::factory(),
            'plant_type' => $this->faker->randomElement(PlantType::cases())->value,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PlantProperties $properties): void {
            if ($this->faker->boolean() && $properties->growthStyles()->doesntExist()) {
                PlantPropertiesGrowthStyle::factory()->for($properties)->create();
            }
        });
    }
}
