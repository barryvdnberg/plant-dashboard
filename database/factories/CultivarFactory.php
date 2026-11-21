<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PlantProperties;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cultivar>
 */
class CultivarFactory extends Factory
{
    // public function hasProperties()
    // {
    //     return $this->has(
    //         PlantProperties::factory(),
    //     );
    // }
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->colorName,
            'image_paths' => [],
        ];
    }
}
