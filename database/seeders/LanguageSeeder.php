<?php

namespace Database\Seeders;

use App\Models\Language;
use Database\Seeders\Concerns\SeedsNameTranslations;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    use SeedsNameTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'code' => 'nl',
                'name' => 'Nederlands',
                'english_name' => 'Dutch',
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'english_name' => 'English',
            ],
            [
                'code' => 'es',
                'name' => 'Español',
                'english_name' => 'Spanish',
            ],
            [
                'code' => 'de',
                'name' => 'Deutsch',
                'english_name' => 'German',
            ],
            [
                'code' => 'fr',
                'name' => 'Français',
                'english_name' => 'French',
            ],
            [
                'code' => 'ja',
                'name' => '日本語',
                'english_name' => 'Japanese',
            ],
            [
                'code' => 'zh',
                'name' => '中文',
                'english_name' => 'Chinese',
            ],
            [
                'code' => 'pt',
                'name' => 'Português',
                'english_name' => 'Portuguese',
            ],
        ];

        foreach ($data as $language) {
            Language::create([
                'code' => $language['code'],
                'name' => $language['name'],
                'english_name' => $language['english_name'],
            ]);
        }

        $translations = [
            'nl' => [
                'nl' => 'Nederlands',
                'en' => 'Dutch',
                'es' => 'Holandés',
                'de' => 'Niederländisch',
                'fr' => 'Néerlandais',
                'ja' => 'オランダ語',
                'zh' => '荷兰语',
                'pt' => 'Holandês',
            ], 'en' => [
                'nl' => 'Engels',
                'en' => 'English',
                'es' => 'Inglés',
                'de' => 'Englisch',
                'fr' => 'Anglais',
                'ja' => '英語',
                'zh' => '英语',
                'pt' => 'Inglês',
            ],
            'es' => [
                'nl' => 'Spaans',
                'en' => 'Spanish',
                'es' => 'Español',
                'de' => 'Spanisch',
                'fr' => 'Espagnol',
                'ja' => 'スペイン語',
                'zh' => '西班牙语',
                'pt' => 'Espanhol',
            ],
            'de' => [
                'nl' => 'Duits',
                'en' => 'German',
                'es' => 'Alemán',
                'de' => 'Deutsch',
                'fr' => 'Allemand',
                'ja' => 'ドイツ語',
                'zh' => '德语',
                'pt' => 'Alemão',
            ],
            'fr' => [
                'nl' => 'Frans',
                'en' => 'French',
                'es' => 'Francés',
                'de' => 'Französisch',
                'fr' => 'Français',
                'ja' => 'フランス語',
                'zh' => '法语',
                'pt' => 'Francês',
            ],
            'ja' => [
                'nl' => 'Japans',
                'en' => 'Japanese',
                'es' => 'Japonés',
                'de' => 'Japanisch',
                'fr' => 'Japonais',
                'ja' => '日本語',
                'zh' => '日语',
                'pt' => 'Japonês',
            ],
            'zh' => [
                'nl' => 'Chinees',
                'en' => 'Chinese',
                'es' => 'Chino',
                'de' => 'Chinesisch',
                'fr' => 'Chinois',
                'ja' => '中国語',
                'zh' => '中文',
                'pt' => 'Chinês',
            ],
            'pt' => [
                'nl' => 'Portugees',
                'en' => 'Portuguese',
                'es' => 'Portugués',
                'de' => 'Portugiesisch',
                'fr' => 'Portugais',
                'ja' => 'ポルトガル語',
                'zh' => '葡萄牙语',
                'pt' => 'Português',
            ],
        ];

        foreach (Language::all() as $language) {
            if (isset($translations[$language->code])) {
                $this->seedNameTranslations($language, $translations[$language->code]);
            }
        }
    }
}
