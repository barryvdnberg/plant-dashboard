<?php

namespace Database\Factories;

use App\Enums\LeafRetention;
use App\Enums\Season;
use App\Models\Color;
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

    public function configure(): static
    {
        return $this->afterCreating(function (Foliage $foliage): void {
            $tags = ['default'];

            if ($foliage->seasonal_color_change) {
                $tags = [...$tags, ...array_column(Season::cases(), 'value')];
            }

            foreach ($tags as $tag) {
                $colors = Color::factory()->count($this->faker->numberBetween(1, 3))->create();
                $foliage->colors()->attach($colors->modelKeys(), ['tag' => $tag]);
            }
        });
    }
}
