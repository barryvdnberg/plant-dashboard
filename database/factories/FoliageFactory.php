<?php

namespace Database\Factories;

use App\Enums\LeafRetention;
use App\Models\Cultivar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Foliage>
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
        $cultivarId = Cultivar::query()->inRandomOrder()->value('id')
            ?? Cultivar::factory()->create()->id;

        return [
            'cultivar_id' => $cultivarId,
            'retention' => $this->faker->randomElement([null, $this->faker->randomElement(LeafRetention::cases())]),
            'seasonal_color_change' => $this->faker->randomElement([0, 0, 1])
        ];
    }
}
