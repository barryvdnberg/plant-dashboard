<?php

namespace Database\Seeders;

use App\Models\Family;
use Illuminate\Database\Seeder;
use App\Traits\HasTranslations;

class FamilySeeder extends Seeder
{
    use HasTranslations;

    public function run(): void
    {
        $families = [
            'Rosaceae' => [
                'nl' => 'Rozenfamilie',
                'en' => 'Rose Family',
                'es' => 'Familia de las Rosáceas',
                'de' => 'Rosengewächse',
                'fr' => 'Rosacées',
                'ja' => 'バラ科',
                'zh' => '蔷薇科',
                'pt' => 'Família das Rosáceas',
            ],
            'Asteraceae' => [
                'nl' => 'Composietenfamilie',
                'en' => 'Aster Family',
                'es' => 'Familia Asteraceae',
                'de' => 'Korbblütler',
                'fr' => 'Astéracées',
                'ja' => 'キク科',
                'zh' => '菊科',
                'pt' => 'Família Asteraceae',
            ],
            'Lamiaceae' => [
                'nl' => 'Lipbloemenfamilie',
                'en' => 'Mint Family',
                'es' => 'Familia de las Lamiáceas',
                'de' => 'Lippenblütler',
                'fr' => 'Lamiacées',
                'ja' => 'シソ科',
                'zh' => '唇形科',
                'pt' => 'Família Lamiaceae',
            ],
        ];

        foreach ($families as $name => $translations) {
            $family = Family::create(['name' => $name]);
            $this->applyTranslations($family, $families);
        }
    }
}
