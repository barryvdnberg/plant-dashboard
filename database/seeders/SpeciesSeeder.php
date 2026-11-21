<?php

namespace Database\Seeders;

use App\Models\Genus;
use App\Models\Species;
use Illuminate\Database\Seeder;
use App\Traits\HasTranslations;

class SpeciesSeeder extends Seeder
{
    use HasTranslations;

    public function run(): void
    {
        $speciesData = [
            'Rugosa' => [
                'nl' => 'Rimpelroos',
                'en' => 'Rugosa Rose',
                'es' => 'Rosa Rugosa',
                'de' => 'Kartoffelrose',
                'fr' => 'Rosier Rugueux',
                'ja' => 'ハマナシ',
                'zh' => '皱叶蔷薇',
                'pt' => 'Rosa Rugosa',
            ],
            'Canina' => [
                'nl' => 'Hondsroos',
                'en' => 'Dog Rose',
                'es' => 'Rosa Canina',
                'de' => 'Hunds-Rose',
                'fr' => 'Églantier',
                'ja' => 'ノイバラ',
                'zh' => '犬蔷薇',
                'pt' => 'Rosa Canina',
            ],
            'Ananassa' => [
                'nl' => 'Tuin aardbei',
                'en' => 'Garden Strawberry',
                'es' => 'Fresa de Jardín',
                'de' => 'Garten-Erdbeere',
                'fr' => 'Fraisier Ananas',
                'ja' => 'オランダイチゴ',
                'zh' => '园草莓',
                'pt' => 'Morango de Jardim',
            ],
            'Vesca' => [
                'nl' => 'Bosaardbei',
                'en' => 'Wild Strawberry',
                'es' => 'Fresa Silvestre',
                'de' => 'Wald-Erdbeere',
                'fr' => 'Fraisier des Bois',
                'ja' => 'ヨーロッパイチゴ',
                'zh' => '欧洲草莓',
                'pt' => 'Morango Silvestre',
            ],
            'Officinale' => [
                'nl' => 'Gewone paardenbloem',
                'en' => 'Common Dandelion',
                'es' => 'Diente de León Común',
                'de' => 'Gewöhnlicher Löwenzahn',
                'fr' => 'Pissenlit Commun',
                'ja' => 'セイヨウタンポポ',
                'zh' => '西洋蒲公英',
                'pt' => 'Dente-de-leão Comum',
            ],
            'Erythrospermum' => [
                'nl' => 'Roodzadige paardenbloem',
                'en' => 'Red-seeded Dandelion',
                'es' => 'Diente de León de Semilla Roja',
                'de' => 'Rotfrüchtiger Löwenzahn',
                'fr' => 'Pissenlit à Graines Rouges',
                'ja' => 'アカネタンポポ',
                'zh' => '红籽蒲公英',
                'pt' => 'Dente-de-leão de Semente Vermelha',
            ],
            'Perennis' => [
                'nl' => 'Gewoon madeliefje',
                'en' => 'Common Daisy',
                'es' => 'Margarita Común',
                'de' => 'Gänseblümchen',
                'fr' => 'Pâquerette Vivace',
                'ja' => 'ヒナギク',
                'zh' => '雏菊',
                'pt' => 'Margarida Comum',
            ],
            'Sylvestris' => [
                'nl' => 'Bosmadeliefje',
                'en' => 'Woodland Daisy',
                'es' => 'Margarita del Bosque',
                'de' => 'Wald-Gänseblümchen',
                'fr' => 'Pâquerette des Bois',
                'ja' => 'モリヒナギク',
                'zh' => '林地雏菊',
                'pt' => 'Margarida da Floresta',
            ],
            'Spicata' => [
                'nl' => 'Groene munt',
                'en' => 'Spearmint',
                'es' => 'Menta Verde',
                'de' => 'Grüne Minze',
                'fr' => 'Menthe Verte',
                'ja' => 'スペアミント',
                'zh' => '绿薄荷',
                'pt' => 'Hortelã Verde',
            ],
            'Aquatica' => [
                'nl' => 'Watermunt',
                'en' => 'Water Mint',
                'es' => 'Menta Acuática',
                'de' => 'Wasserminze',
                'fr' => 'Menthe Aquatique',
                'ja' => 'ウォーターミント',
                'zh' => '水薄荷',
                'pt' => 'Hortelã Aquática',
            ],
            'Officinalis' => [
                'nl' => 'Echte salie',
                'en' => 'Common Sage',
                'es' => 'Salvia Común',
                'de' => 'Gemeiner Salbei',
                'fr' => 'Sauge Officinale',
                'ja' => 'セージ',
                'zh' => '药用鼠尾草',
                'pt' => 'Sálvia Comum',
            ],
            'Nemorosa' => [
                'nl' => 'Bossalie',
                'en' => 'Woodland Sage',
                'es' => 'Salvia del Bosque',
                'de' => 'Wald-Salbei',
                'fr' => 'Sauge des Bois',
                'ja' => 'ネモローサ・セージ',
                'zh' => '林地鼠尾草',
                'pt' => 'Sálvia da Floresta',
            ],
        ];

        $genusMapping = [
            'Rosa' => ['Rugosa', 'Canina'],
            'Fragaria' => ['Ananassa', 'Vesca'],
            'Taraxacum' => ['Officinale', 'Erythrospermum'],
            'Bellis' => ['Perennis', 'Sylvestris'],
            'Mentha' => ['Spicata', 'Aquatica'],
            'Salvia' => ['Officinalis', 'Nemorosa'],
        ];

        foreach ($genusMapping as $genusName => $speciesNames) {

            $genus = Genus::where('name', $genusName)->first();

            foreach ($speciesNames as $sp) {
                $species = $genus->species()->create(['name' => $sp]);
                $this->applyTranslations($species, $speciesData);
            }
        }
    }
}
