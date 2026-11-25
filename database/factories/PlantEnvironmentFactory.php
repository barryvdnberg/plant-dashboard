<?php

namespace Database\Factories;

use App\Enums\GroundNutrition;
use App\Enums\HardinessZone;
use App\Enums\Moisture;
use App\Enums\SunExposure;
use App\Models\Cultivar;
use App\Models\PlantEnvironment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantEnvironment>
 */
class PlantEnvironmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $minimumPh = $this->faker->numberBetween(4, 7);

        return [
            'cultivar_id' => Cultivar::factory(),
            'winter_hardiness' => $this->faker->randomElement(HardinessZone::cases()),
            'ph_range' => [$minimumPh, $minimumPh + $this->faker->numberBetween(1, 2)],
            'ground_nutrition_minimum' => $this->faker->optional()->randomElement(GroundNutrition::cases()),
            'soil_moisture' => $this->faker->optional()->randomElement(Moisture::cases()),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PlantEnvironment $environment): void {
            $exposures = $this->faker->randomElements(SunExposure::cases(), $this->faker->numberBetween(1, 2));

            $environment->sunExposures()->createMany(
                array_map(fn (SunExposure $exposure): array => ['value' => $exposure->value], $exposures),
            );
        });
    }
}
