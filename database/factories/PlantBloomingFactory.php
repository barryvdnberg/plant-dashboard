<?php

namespace Database\Factories;

use App\Enums\Month;
use App\Models\Cultivar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlantBlooming>
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
        $cultivarId = Cultivar::query()->inRandomOrder()->value('id')
            ?? Cultivar::factory()->create()->id;

        $months = collect(Month::cases())->random(number: rand(min: 0, max: 3))->all();
        return [
            'cultivar_id' => $cultivarId,
            // 'months' => $months
        ];
    }
}
