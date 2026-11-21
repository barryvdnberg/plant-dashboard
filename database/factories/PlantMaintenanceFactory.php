<?php

namespace Database\Factories;

use App\Enums\IntensityLevel;
use App\Enums\MaintenanceDifficulty;
use App\Models\Cultivar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlantMaintenance>
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
        $cultivarId = Cultivar::query()->inRandomOrder()->value('id')
            ?? Cultivar::factory()->create()->id;

        $intensities = IntensityLevel::cases();
        $intensities[] = null;
        return [
            'cultivar_id' => $cultivarId,
            'group' => 'default',
            'watering_needs' => $this->faker->randomElement($intensities),
            'maintenance_rich' => $this->faker->boolean(),
            'difficulty' => $this->faker->randomElement([null, $this->faker->randomElement(MaintenanceDifficulty::cases())]),
        ];
    }
}
