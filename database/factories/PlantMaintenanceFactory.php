<?php

namespace Database\Factories;

use App\Enums\GrowthPattern;
use App\Enums\IntensityLevel;
use App\Enums\MaintenanceDifficulty;
use App\Models\Cultivar;
use App\Models\PlantMaintenance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantMaintenance>
 */
class PlantMaintenanceFactory extends Factory
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
            'group' => GrowthPattern::DEFAULT,
            'watering_needs' => $this->faker->optional()->randomElement(IntensityLevel::cases()),
            'maintenance_rich' => $this->faker->boolean(),
            'difficulty' => $this->faker->optional()->randomElement(MaintenanceDifficulty::cases()),
        ];
    }
}
