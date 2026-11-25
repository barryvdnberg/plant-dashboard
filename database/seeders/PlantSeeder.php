<?php

namespace Database\Seeders;

use App\Enums\PlantType;
use App\Models\Color;
use App\Models\Cultivar;
use App\Models\Family;
use App\Models\SoilType;
use Database\Seeders\Concerns\SeedsNameTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class PlantSeeder extends Seeder
{
    use SeedsNameTranslations;

    public function run(): void
    {
        $colorIds = Color::query()->pluck('id', 'name');
        $soilIds = SoilType::query()->pluck('id', 'name');

        foreach (StarterPlantCatalog::TAXONOMY as $familyName => $genera) {
            $family = Family::whereName($familyName)->firstOrFail();

            foreach ($genera as $genusName => $entries) {
                $genus = $family->genera()->whereName($genusName)->firstOrFail();

                foreach ($entries as $epithet => [$type, $names]) {
                    $scientificName = "$genusName $epithet";
                    $species = $genus->species()->whereName($epithet)->firstOrFail();
                    $plant = $species->plants()->firstOrCreate(['name' => $scientificName]);

                    $this->seedNameTranslations($plant, $names);

                    $base = $plant->baseCultivar()->firstOrNew(['name' => null]);
                    if (! $base->is_default) {
                        $base->forceFill(['is_default' => true])->save();
                    }

                    $this->seedDetails($base, $type, StarterPlantCatalog::SPECIES_PROFILES[$scientificName] ?? [], $colorIds, $soilIds);

                    foreach (StarterPlantCatalog::CULTIVARS[$scientificName] ?? [] as $name => $profile) {
                        $cultivar = $plant->cultivars()->firstOrCreate(['name' => $name]);
                        $this->seedDetails($cultivar, $type, $profile, $colorIds, $soilIds);
                    }
                }
            }
        }
    }

    private function seedDetails(Cultivar $cultivar, PlantType $type, array $profile, Collection $colorIds, Collection $soilIds): void
    {
        $properties = $cultivar->properties()->firstOrCreate([], ['plant_type' => $type->value]);
        foreach ($profile['growth_styles'] ?? [] as $style) {
            $properties->growthStyles()->firstOrCreate(['name' => $style->value]);
        }

        if (isset($profile['retention'])) {
            $cultivar->foliage()->firstOrCreate([], ['retention' => $profile['retention']->value]);
        }

        if (isset($profile['hardiness']) || isset($profile['moisture']) || isset($profile['sun']) || isset($profile['soil'])) {
            $environment = $cultivar->environment()->firstOrCreate([], [
                'winter_hardiness' => $profile['hardiness'] ?? null,
                // Sample environments use the form's default pH range.
                'ph_range' => [6, 8],
                'soil_moisture' => $profile['moisture'] ?? null,
            ]);

            foreach ($profile['sun'] ?? [] as $exposure) {
                $environment->sunExposures()->firstOrCreate(['value' => $exposure->value]);
            }

            $environment->soilTypes()->syncWithoutDetaching($soilIds->only($profile['soil'] ?? [])->values()->all());
        }

        if (isset($profile['flower_colors']) || isset($profile['months'])) {
            $blooming = $cultivar->blooming()->firstOrCreate();

            foreach ($profile['months'] ?? [] as $month) {
                $blooming->months()->firstOrCreate(['index' => $month->value]);
            }

            $blooming->colors()->syncWithoutDetaching($colorIds->only($profile['flower_colors'] ?? [])->values()->all());
        }
    }
}
