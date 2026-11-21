<?php

namespace Database\Seeders;

use App\Models\GardenStyle;
use App\Traits\HasTranslations;
use Illuminate\Database\Seeder;

class GardenStyleSeeder extends Seeder
{
    use HasTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $data = [
            ['name' => 'Japanese Garden'],
            ['name' => 'Modern Garden'],
            ['name' => 'Wild Garden'],
            ['name' => 'English Garden'],
            ['name' => 'Dutch Garden'],
            ['name' => 'Mediterranean Garden'],
            ['name' => 'Tropical Garden'],
            ['name' => 'Cottage Garden'],
            ['name' => 'Rock Garden'],
            ['name' => 'Zen Garden'],
        ];

        $translations = [
            'Japanese Garden' => [
                'nl' => 'Japanse tuin',
                'en' => 'Japanese Garden',
                'es' => 'Jardín japonés',
                'de' => 'Japanischer Garten',
                'fr' => 'Jardin japonais',
                'ja' => '日本庭園',
                'zh' => '日本庭园',
                'pt' => 'Jardim japonês',
            ],
            'Modern Garden' => [
                'nl' => 'Moderne tuin',
                'en' => 'Modern Garden',
                'es' => 'Jardín moderno',
                'de' => 'Moderner Garten',
                'fr' => 'Jardin moderne',
                'ja' => 'モダンガーデン',
                'zh' => '现代花园',
                'pt' => 'Jardim moderno',
            ],
            'Wild Garden' => [
                'nl' => 'Wilde tuin',
                'en' => 'Wild Garden',
                'es' => 'Jardín silvestre',
                'de' => 'Wilder Garten',
                'fr' => 'Jardin sauvage',
                'ja' => 'ワイルドガーデン',
                'zh' => '野生花园',
                'pt' => 'Jardim selvagem',
            ],
            'English Garden' => [
                'nl' => 'Engelse tuin',
                'en' => 'English Garden',
                'es' => 'Jardín inglés',
                'de' => 'Englischer Garten',
                'fr' => 'Jardin anglais',
                'ja' => 'イングリッシュガーデン',
                'zh' => '英式花园',
                'pt' => 'Jardim inglês',
            ],
            'Dutch Garden' => [
                'nl' => 'Nederlandse tuin',
                'en' => 'Dutch Garden',
                'es' => 'Jardín neerlandés',
                'de' => 'Niederländischer Garten',
                'fr' => 'Jardin néerlandais',
                'ja' => 'オランダ庭園',
                'zh' => '荷兰花园',
                'pt' => 'Jardim holandês',
            ],
            'Mediterranean Garden' => [
                'nl' => 'Mediterrane tuin',
                'en' => 'Mediterranean Garden',
                'es' => 'Jardín mediterráneo',
                'de' => 'Mediterraner Garten',
                'fr' => 'Jardin méditerranéen',
                'ja' => '地中海風ガーデン',
                'zh' => '地中海花园',
                'pt' => 'Jardim mediterrânico',
            ],
            'Tropical Garden' => [
                'nl' => 'Tropische tuin',
                'en' => 'Tropical Garden',
                'es' => 'Jardín tropical',
                'de' => 'Tropischer Garten',
                'fr' => 'Jardin tropical',
                'ja' => 'トロピカルガーデン',
                'zh' => '热带花园',
                'pt' => 'Jardim tropical',
            ],
            'Cottage Garden' => [
                'nl' => 'Cottage tuin',
                'en' => 'Cottage Garden',
                'es' => 'Jardín campestre',
                'de' => 'Bauerngarten',
                'fr' => 'Jardin de cottage',
                'ja' => 'コテージガーデン',
                'zh' => '乡村花园',
                'pt' => 'Jardim de campo',
            ],
            'Rock Garden' => [
                'nl' => 'Rotstuin',
                'en' => 'Rock Garden',
                'es' => 'Jardín de rocas',
                'de' => 'Steingarten',
                'fr' => 'Jardin de rocaille',
                'ja' => 'ロックガーデン',
                'zh' => '石头花园',
                'pt' => 'Jardim de pedras',
            ],
            'Zen Garden' => [
                'nl' => 'Zen tuin',
                'en' => 'Zen Garden',
                'es' => 'Jardín zen',
                'de' => 'Zen-Garten',
                'fr' => 'Jardin zen',
                'ja' => '禅庭園',
                'zh' => '禅意花园',
                'pt' => 'Jardim zen',
            ],
        ];

        foreach ($data as $info) {
            $style = GardenStyle::updateOrCreate(['name' => $info['name']]);

            $this->applyTranslations($style, $translations);
        }
    }
}
