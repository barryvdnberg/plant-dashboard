<?php

namespace Database\Factories;

use App\Enums\GroundNutrition;
use App\Enums\HardinessZone;
use App\Enums\Moisture;
use App\Enums\SunExposure;
use App\Models\Cultivar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlantEnvironment>
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
        $cultivarId = Cultivar::query()->inRandomOrder()->value('id')
            ?? Cultivar::factory()->create()->id;

        $ph_min = $this->faker->numberBetween(1, 9);
        $ph_max = $ph_min + $this->faker->numberBetween(0, 4);
        
        $exposures = collect(SunExposure::cases())->random(number: rand(min: 0, max: 2))->all();
        return [
            'cultivar_id' => $cultivarId,
            'winter_hardiness' => $this->faker->randomElement([null, null, $this->faker->randomElement(HardinessZone::cases())]),
            // 'sun_exposure' => $exposures,
            'ph_range' => [$ph_min, $ph_max],
            'ground_nutrition_minimum' => $this->faker->randomElement([null, null, $this->faker->randomElement(GroundNutrition::cases())]),
            'soil_moisture' => $this->faker->randomElement([null, null, $this->faker->randomElement(Moisture::cases())])
        ];
    }
}
