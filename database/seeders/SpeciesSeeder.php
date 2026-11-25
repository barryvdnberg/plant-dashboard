<?php

namespace Database\Seeders;

use App\Models\Family;
use Database\Seeders\Concerns\SeedsNameTranslations;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    use SeedsNameTranslations;

    public function run(): void
    {
        foreach (StarterPlantCatalog::TAXONOMY as $familyName => $genera) {
            $family = Family::whereName($familyName)->firstOrFail();

            foreach ($genera as $genusName => $entries) {
                $genus = $family->genera()->whereName($genusName)->firstOrFail();

                foreach ($entries as $epithet => [, $names]) {
                    $species = $genus->species()->firstOrCreate(['name' => $epithet]);

                    $this->seedNameTranslations($species, $names);
                }
            }
        }
    }
}
