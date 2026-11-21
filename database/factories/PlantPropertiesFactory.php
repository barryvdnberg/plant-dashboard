<?php

namespace Database\Factories;

use App\Enums\GrowthPattern;
use App\Enums\PlantTrait;
use App\Enums\PlantType;
use App\Models\Cultivar;
use App\Models\PlantProperties;
use App\Models\PlantPropertiesGrowthStyle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlantProperties>
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
            'plant_type' => $this->faker->randomElement([
                null, 
                $this->faker->randomElement(PlantType::cases())
            ]),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PlantProperties $properties) {
            if ($this->faker->boolean()) {
                PlantPropertiesGrowthStyle::factory()
                    ->for($properties)
                    ->create(); 
            }
        });
    }
}
