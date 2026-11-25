<?php

namespace Database\Seeders;

use App\Models\SoilType;
use Database\Seeders\Concerns\SeedsNameTranslations;
use Illuminate\Database\Seeder;

class SoilTypeSeeder extends Seeder
{
    use SeedsNameTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $data = [
            ['name' => 'Sand'],
            ['name' => 'Chalk'],
            ['name' => 'Peat'],
            ['name' => 'Clay'],
            ['name' => 'Silt'],
            ['name' => 'Loam'],
        ];

        $translations = [
            'Sand' => [
                'nl' => 'Zand',
                'en' => 'Sand',
                'es' => 'Arena',
                'de' => 'Sand',
                'fr' => 'Sable',
                'ja' => '砂',
                'zh' => '沙',
                'pt' => 'Areia',
            ],
            'Chalk' => [
                'nl' => 'Kalk',
                'en' => 'Chalk',
                'es' => 'Tiza',
                'de' => 'Kalkstein',
                'fr' => 'Craie',
                'ja' => 'チョーク',
                'zh' => '白垩',
                'pt' => 'Giz',
            ],
            'Peat' => [
                'nl' => 'Veen',
                'en' => 'Peat',
                'es' => 'Turba',
                'de' => 'Torf',
                'fr' => 'Tourbe',
                'ja' => '泥炭',
                'zh' => '泥炭',
                'pt' => 'Turfa',
            ],
            'Clay' => [
                'nl' => 'Klei',
                'en' => 'Clay',
                'es' => 'Arcilla',
                'de' => 'Ton',
                'fr' => 'Argile',
                'ja' => '粘土',
                'zh' => '黏土',
                'pt' => 'Argila',
            ],
            'Silt' => [
                'nl' => 'Slib',
                'en' => 'Silt',
                'es' => 'Limo',
                'de' => 'Schluff',
                'fr' => 'Limons',
                'ja' => 'シルト',
                'zh' => '淤泥',
                'pt' => 'Silte',
            ],
            'Loam' => [
                'nl' => 'Leem',
                'en' => 'Loam',
                'es' => 'Franco',
                'de' => 'Lehm',
                'fr' => 'Terreau',
                'ja' => 'ローム',
                'zh' => '壤土',
                'pt' => 'Solo argiloso',
            ],
        ];

        foreach ($data as $info) {
            $soil = SoilType::firstOrCreate(['name' => $info['name']]);

            $this->seedNameTranslations($soil, $translations[$info['name']]);
        }
    }
}
