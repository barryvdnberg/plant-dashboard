<?php

namespace Database\Seeders;

use App\Models\Color;
use Database\Seeders\Concerns\SeedsNameTranslations;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    use SeedsNameTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $data = [
            ['name' => 'Purple', 'hex_value' => '#800080'],
            ['name' => 'Pink', 'hex_value' => '#FFC0CB'],
            ['name' => 'Red', 'hex_value' => '#FF0000'],
            ['name' => 'Orange', 'hex_value' => '#FFA500'],
            ['name' => 'Gold', 'hex_value' => '#FFD700'],
            ['name' => 'Yellow', 'hex_value' => '#FFFF00'],
            ['name' => 'Lime', 'hex_value' => '#DBFF00'],
            ['name' => 'Green', 'hex_value' => '#00B000'],
            ['name' => 'Blue', 'hex_value' => '#0000FF'],
            ['name' => 'Cyan', 'hex_value' => '#00FFFF'],
            ['name' => 'White', 'hex_value' => '#FFFFFF'],
            ['name' => 'Gray', 'hex_value' => '#808080'],
            ['name' => 'Brown', 'hex_value' => '#964B00'],
            ['name' => 'Black', 'hex_value' => '#000000'],
        ];

        $translations = [
            'Purple' => [
                'nl' => 'Paars',
                'en' => 'Purple',
                'es' => 'Púrpura',
                'de' => 'Lila',
                'fr' => 'Violet',
                'ja' => '紫',
                'zh' => '紫色',
                'pt' => 'Roxo',
            ],
            'Pink' => [
                'nl' => 'Roze',
                'en' => 'Pink',
                'es' => 'Rosa',
                'de' => 'Rosa',
                'fr' => 'Rose',
                'ja' => 'ピンク',
                'zh' => '粉色',
                'pt' => 'Rosa',
            ],
            'Red' => [
                'nl' => 'Rood',
                'en' => 'Red',
                'es' => 'Rojo',
                'de' => 'Rot',
                'fr' => 'Rouge',
                'ja' => '赤',
                'zh' => '红色',
                'pt' => 'Vermelho',
            ],
            'Orange' => [
                'nl' => 'Oranje',
                'en' => 'Orange',
                'es' => 'Naranja',
                'de' => 'Orange',
                'fr' => 'Orange',
                'ja' => 'オレンジ',
                'zh' => '橙色',
                'pt' => 'Laranja',
            ],
            'Gold' => [
                'nl' => 'Goud',
                'en' => 'Gold',
                'es' => 'Dorado',
                'de' => 'Gold',
                'fr' => 'Or',
                'ja' => '金色',
                'zh' => '金色',
                'pt' => 'Dourado',
            ],
            'Yellow' => [
                'nl' => 'Geel',
                'en' => 'Yellow',
                'es' => 'Amarillo',
                'de' => 'Gelb',
                'fr' => 'Jaune',
                'ja' => '黄色',
                'zh' => '黄色',
                'pt' => 'Amarelo',
            ],
            'Lime' => [
                'nl' => 'Limoen',
                'en' => 'Lime',
                'es' => 'Lima',
                'de' => 'Limette',
                'fr' => 'Citron vert',
                'ja' => 'ライム',
                'zh' => '酸橙色',
                'pt' => 'Limão',
            ],
            'Green' => [
                'nl' => 'Groen',
                'en' => 'Green',
                'es' => 'Verde',
                'de' => 'Grün',
                'fr' => 'Vert',
                'ja' => '緑色',
                'zh' => '绿色',
                'pt' => 'Verde',
            ],
            'Blue' => [
                'nl' => 'Blauw',
                'en' => 'Blue',
                'es' => 'Azul',
                'de' => 'Blau',
                'fr' => 'Bleu',
                'ja' => '青',
                'zh' => '蓝色',
                'pt' => 'Azul',
            ],
            'Cyan' => [
                'nl' => 'Cyaan',
                'en' => 'Cyan',
                'es' => 'Cian',
                'de' => 'Cyan',
                'fr' => 'Cyan',
                'ja' => 'シアン',
                'zh' => '青色',
                'pt' => 'Ciano',
            ],
            'White' => [
                'nl' => 'Wit',
                'en' => 'White',
                'es' => 'Blanco',
                'de' => 'Weiß',
                'fr' => 'Blanc',
                'ja' => '白',
                'zh' => '白色',
                'pt' => 'Branco',
            ],
            'Gray' => [
                'nl' => 'Grijs',
                'en' => 'Gray',
                'es' => 'Gris',
                'de' => 'Grau',
                'fr' => 'Gris',
                'ja' => 'グレー',
                'zh' => '灰色',
                'pt' => 'Cinza',
            ],
            'Brown' => [
                'nl' => 'Bruin',
                'en' => 'Brown',
                'es' => 'Marrón',
                'de' => 'Braun',
                'fr' => 'Brun',
                'ja' => '茶色',
                'zh' => '棕色',
                'pt' => 'Marrom',
            ],
            'Black' => [
                'nl' => 'Zwart',
                'en' => 'Black',
                'es' => 'Negro',
                'de' => 'Schwarz',
                'fr' => 'Noir',
                'ja' => '黒',
                'zh' => '黑色',
                'pt' => 'Preto',
            ],
        ];

        foreach ($data as $info) {
            $color = Color::create([
                'name' => $info['name'],
                'hex_value' => $info['hex_value'],
            ]);

            $this->seedNameTranslations($color, $translations[$info['name']]);
        }
    }
}
