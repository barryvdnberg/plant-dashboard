<?php

namespace Database\Seeders;

use App\Models\Species;
use App\Models\Plant;
use App\Models\Cultivar;
use Illuminate\Database\Seeder;
use App\Traits\HasTranslations;

class PlantSeeder extends Seeder
{
    use HasTranslations;

    public function run(): void
    {
        $species = Species::all();

        $translations = [];
        
        foreach ($species as $sp) {

            $plantName = $sp->genus->name . ' ' . $sp->name;

            $plant = $sp->plants()->create(['name' => $plantName]);

            // Full 8-language identical names (or translated names if you want)
            $translations[$plantName] = [
                'nl' => $plantName,
                'en' => $plantName,
                'es' => $plantName,
                'de' => $plantName,
                'fr' => $plantName,
                'ja' => $plantName,
                'zh' => $plantName,
                'pt' => $plantName,
            ];

            $this->applyTranslations($plant, $translations);

            // foreach (['Alpha', 'Beta', 'Gamma'] as $suffix) {

            //     $cvName = "$plantName cv. $suffix";

            //     $cultivar = $plant->cultivars()->create([
            //         'name' => $cvName
            //     ]);

            //     $translations[$cvName] = [
            //         'nl' => $cvName,
            //         'en' => $cvName,
            //         'es' => $cvName,
            //         'de' => $cvName,
            //         'fr' => $cvName,
            //         'ja' => $cvName,
            //         'zh' => $cvName,
            //         'pt' => $cvName,
            //     ];

            //     $this->applyTranslations($cultivar, $translations);
            // }
        }
    }
}
