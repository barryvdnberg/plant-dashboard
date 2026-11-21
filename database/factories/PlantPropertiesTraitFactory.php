<?php

namespace Database\Factories;

use App\Enums\PlantTrait;
use App\Models\PlantProperties;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlantPropertiesTrait>
 */
class PlantPropertiesTraitFactory extends Factory
{
    protected $plantPropertiesIds = [];

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->plantPropertiesIds = PlantProperties::all()->pluck('id')->toArray();
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plant_properties_id' => $this->faker->randomElement($this->plantPropertiesIds),
            'trait_index' => $this->faker->randomElement(PlantTrait::cases()),
        ];
    }
}
