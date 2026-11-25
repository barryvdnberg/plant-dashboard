<?php

namespace Database\Factories;

use App\Models\Plant;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plant>
 */
class PlantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'species_id' => Species::factory(),
            'name' => fn (array $attributes): string => $attributes['species_id'] === null
                ? ucfirst($this->faker->word()).' '.$this->faker->word()
                : Species::on($this->connection)->with('genus')->findOrFail($attributes['species_id'])->scientific_name,
            'image_paths' => [],
        ];
    }
}
