<?php

namespace Database\Seeders;

use App\Models\Family;
use Database\Seeders\Concerns\SeedsNameTranslations;
use Illuminate\Database\Seeder;

class GenusSeeder extends Seeder
{
    use SeedsNameTranslations;

    public function run(): void
    {
        $genera = [
            'Rosa' => [
                'nl' => 'Roos',
                'en' => 'Rose',
                'es' => 'Rosa',
                'de' => 'Rose',
                'fr' => 'Rosier',
                'ja' => 'バラ属',
                'zh' => '蔷薇属',
                'pt' => 'Rosa',
            ],
            'Fragaria' => [
                'nl' => 'Aardbei',
                'en' => 'Strawberry',
                'es' => 'Fresa',
                'de' => 'Erdbeere',
                'fr' => 'Fraisier',
                'ja' => 'イチゴ属',
                'zh' => '草莓属',
                'pt' => 'Morangueiro',
            ],
            'Taraxacum' => [
                'nl' => 'Paardenbloem',
                'en' => 'Dandelion',
                'es' => 'Diente de León',
                'de' => 'Löwenzahn',
                'fr' => 'Pissenlit',
                'ja' => 'タンポポ属',
                'zh' => '蒲公英属',
                'pt' => 'Dente-de-leão',
            ],
            'Bellis' => [
                'nl' => 'Madeliefje',
                'en' => 'Daisy',
                'es' => 'Margarita',
                'de' => 'Gänseblümchen',
                'fr' => 'Pâquerette',
                'ja' => 'ヒナギク属',
                'zh' => '雏菊属',
                'pt' => 'Margarida',
            ],
            'Mentha' => [
                'nl' => 'Munt',
                'en' => 'Mint',
                'es' => 'Menta',
                'de' => 'Minze',
                'fr' => 'Menthe',
                'ja' => 'ハッカ属',
                'zh' => '薄荷属',
                'pt' => 'Hortelã',
            ],
            'Salvia' => [
                'nl' => 'Salie',
                'en' => 'Sage',
                'es' => 'Salvia',
                'de' => 'Salbei',
                'fr' => 'Sauge',
                'ja' => 'アキギリ属',
                'zh' => '鼠尾草属',
                'pt' => 'Sálvia',
            ],
        ];

        foreach (StarterPlantCatalog::TAXONOMY as $familyName => $entries) {
            $family = Family::whereName($familyName)->firstOrFail();

            foreach (array_keys($entries) as $genusName) {
                $genus = $family->genera()->firstOrCreate(['name' => $genusName]);
                $this->seedNameTranslations($genus, $genera[$genusName]);
            }
        }
    }
}
