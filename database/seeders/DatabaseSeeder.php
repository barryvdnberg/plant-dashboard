<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(fn () => $this->call([
            LanguageSeeder::class,
            ColorSeeder::class,
            GardenStyleSeeder::class,
            SoilTypeSeeder::class,
            StarterPlantCatalogSeeder::class,
        ]));
    }
}
