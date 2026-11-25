<?php

namespace Database\Factories;

use App\Enums\Month;
use App\Models\Cultivar;
use App\Models\PlantBlooming;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantBlooming>
 */
class PlantBloomingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['cultivar_id' => Cultivar::factory()];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PlantBlooming $blooming): void {
            $months = $this->faker->randomElements(Month::cases(), $this->faker->numberBetween(1, 3));

            $blooming->months()->createMany(
                array_map(fn (Month $month): array => ['index' => $month->value], $months),
            );
        });
    }
}
