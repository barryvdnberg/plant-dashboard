<?php

namespace Database\Seeders;

use App\Enums\GrowthPattern;
use App\Enums\HardinessZone;
use App\Enums\LeafRetention;
use App\Enums\Moisture;
use App\Enums\Month;
use App\Enums\PlantType;
use App\Enums\SunExposure;

final class StarterPlantCatalog
{
    public const TAXONOMY = [
        'Rosaceae' => [
            'Rosa' => [
                'rugosa' => [PlantType::SHRUB, ['en' => 'Rugosa rose', 'nl' => 'Rimpelroos']],
                'canina' => [PlantType::SHRUB, ['en' => 'Dog rose', 'nl' => 'Hondsroos']],
            ],
            'Fragaria' => [
                '× ananassa' => [PlantType::PERENNIAL, ['en' => 'Garden strawberry', 'nl' => 'Tuinaardbei']],
                'vesca' => [PlantType::PERENNIAL, ['en' => 'Wild strawberry', 'nl' => 'Bosaardbei']],
            ],
        ],
        'Asteraceae' => [
            'Taraxacum' => [
                'officinale' => [PlantType::PERENNIAL, ['en' => 'Common dandelion', 'nl' => 'Gewone paardenbloem']],
                'erythrospermum' => [PlantType::PERENNIAL, ['en' => 'Red-seeded dandelion', 'nl' => 'Roodzadige paardenbloem']],
            ],
            'Bellis' => [
                'perennis' => [PlantType::PERENNIAL, ['en' => 'Common daisy', 'nl' => 'Madeliefje']],
                'sylvestris' => [PlantType::PERENNIAL, ['en' => 'Southern daisy', 'nl' => 'Bosmadeliefje']],
            ],
        ],
        'Lamiaceae' => [
            'Mentha' => [
                'spicata' => [PlantType::PERENNIAL, ['en' => 'Spearmint', 'nl' => 'Groene munt']],
                'aquatica' => [PlantType::PERENNIAL, ['en' => 'Water mint', 'nl' => 'Watermunt']],
            ],
            'Salvia' => [
                'officinalis' => [PlantType::SHRUB, ['en' => 'Common sage', 'nl' => 'Echte salie']],
                'nemorosa' => [PlantType::PERENNIAL, ['en' => 'Woodland sage', 'nl' => 'Bossalie']],
            ],
        ],
    ];

    public const SPECIES_PROFILES = [
        'Rosa rugosa' => [
            'growth_styles' => [GrowthPattern::SHRUB],
            'retention' => LeafRetention::DECIDUOUS,
            'hardiness' => HardinessZone::ZONE_3,
            'sun' => [SunExposure::SUN],
            'flower_colors' => ['Pink'],
        ],
        'Mentha aquatica' => [
            'growth_styles' => [GrowthPattern::MARGINAL_PLANT],
            'retention' => LeafRetention::DECIDUOUS,
            'sun' => [SunExposure::SUN, SunExposure::HALF_SHADE],
            'moisture' => Moisture::WET,
        ],
        'Salvia officinalis' => [
            'growth_styles' => [GrowthPattern::SHRUB],
            'retention' => LeafRetention::EVERGREEN,
            'sun' => [SunExposure::SUN],
            'flower_colors' => ['Blue', 'Purple'],
        ],
    ];

    public const CULTIVARS = [
        'Rosa rugosa' => [
            'Alba' => [
                'growth_styles' => [GrowthPattern::SHRUB],
                'retention' => LeafRetention::DECIDUOUS,
                'hardiness' => HardinessZone::ZONE_3,
                'sun' => [SunExposure::SUN, SunExposure::HALF_SHADE],
                'soil' => ['Loam'],
                'flower_colors' => ['White'],
                'months' => [Month::JUNE, Month::JULY, Month::AUGUST],
            ],
        ],
        'Fragaria × ananassa' => [
            'Elsanta' => ['growth_styles' => [GrowthPattern::FRUIT_CROP]],
        ],
        'Bellis perennis' => [
            'Robella' => [],
        ],
        'Salvia officinalis' => [
            'Icterina' => [
                'growth_styles' => [GrowthPattern::SHRUB],
                'retention' => LeafRetention::EVERGREEN,
                'sun' => [SunExposure::SUN],
                'soil' => ['Chalk', 'Loam', 'Sand'],
                'flower_colors' => ['Blue', 'Purple'],
            ],
        ],
        'Salvia nemorosa' => [
            'Caradonna' => [
                'retention' => LeafRetention::DECIDUOUS,
                'sun' => [SunExposure::SUN, SunExposure::HALF_SHADE],
                'soil' => ['Chalk', 'Loam', 'Sand'],
                'flower_colors' => ['Purple'],
            ],
            'New Dimension Rose' => [
                'hardiness' => HardinessZone::ZONE_5,
                'sun' => [SunExposure::SUN],
                'soil' => ['Sand'],
                'flower_colors' => ['Pink'],
                'months' => [Month::MAY, Month::JUNE, Month::JULY, Month::AUGUST],
            ],
        ],
    ];
}
