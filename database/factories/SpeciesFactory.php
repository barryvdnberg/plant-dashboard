<?php

namespace Database\Factories;

use App\Models\Genus;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Species>
 */
class SpeciesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'genus_id' => Genus::factory(),
            'name' => $this->faker->word(),
            'image_paths' => [],
        ];
    }
}
