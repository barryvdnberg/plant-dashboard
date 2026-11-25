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
}
