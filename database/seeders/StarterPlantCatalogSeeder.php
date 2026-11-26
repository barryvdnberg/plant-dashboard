<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StarterPlantCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(fn () => $this->call([
            FamilySeeder::class,
            GenusSeeder::class,
            SpeciesSeeder::class,
            PlantSeeder::class,
        ]));
    }
}
