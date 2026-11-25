<?php

namespace Database\Factories;

use App\Models\Cultivar;
use App\Models\Garden;
use App\Models\GardenPlant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GardenPlant>
 */
class GardenPlantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'garden_id' => Garden::factory(),
            'cultivar_id' => Cultivar::factory(),
            'name' => ucfirst($this->faker->words(2, true)),
            'size' => $this->faker->randomFloat(2, 5, 300),
        ];
    }
}
