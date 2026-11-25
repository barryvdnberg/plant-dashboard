<?php

namespace Database\Factories;

use App\Enums\LeafRetention;
use App\Models\Cultivar;
use App\Models\Foliage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Foliage>
 */
class FoliageFactory extends Factory
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
            'retention' => $this->faker->optional()->randomElement(LeafRetention::cases())?->value,
            'seasonal_color_change' => $this->faker->boolean(25),
        ];
    }
}
