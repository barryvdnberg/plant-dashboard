<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum GrowthPattern: string implements HasLabel
{
    case SHRUB = 'shrub';
    case TREE = 'tree';
    case CONIFER = 'conifer';
    case FRUIT_TREE = 'fruit tree';
    case FRUIT_CROP = 'fruit crop';
    case BULB_TREE = 'bulb tree';
    case ESPALIER_TREE = 'espalier tree';
    case POLLARDED_TREE = 'pollarded tree';
    case STANDARD_TREE = 'standard tree';
    case HALF_STANDARD_TREE = 'half-standard tree';
    case COLUMNAR_TREE = 'columnar tree';
    case ROOF_TREE = 'roof tree';
    case DECIDUOUS_TREE = 'deciduous tree';
    case MULTI_STEMMED_TREE = 'multi-stemmed tree';
    case ESPALIER = 'espalier';
    case ROCK_PLANT = 'rock plant';
    case AQUATIC_PLANT = 'aquatic plant';
    case MARGINAL_PLANT = 'marginal plant';
    case TOPIARY = 'topiary';
    case SPECIMEN = 'specimen';
    case HEDGE = 'hedge';
    case ORNAMENTAL_GRASS = 'ornamental grass';
    case SUCCULENTS = 'succulents';
    case GROUNDCOVER = 'groundcover';
    case OXYGENATING_PLANT = 'oxygenating plant';
    case FLOATING_PLANT = 'floating plant';
    case MOSSES = 'mosses';
    case UNDERWATER_MOSSES_FEATHER_FORMS = 'underwater mosses / feather forms';
    case DEFAULT = 'default';

    public function getLabel(): string|Htmlable|null
    {
         return match ($this) {
            self::DEFAULT => __('enums.growth_pattern.default'),
            self::SHRUB => __('enums.growth_pattern.shrub'),
            self::TREE => __('enums.growth_pattern.tree'),
            self::CONIFER => __('enums.growth_pattern.conifer'),
            self::FRUIT_TREE => __('enums.growth_pattern.fruit_tree'),
            self::FRUIT_CROP => __('enums.growth_pattern.fruit_crop'),
            self::BULB_TREE => __('enums.growth_pattern.bulb_tree'),
            self::ESPALIER_TREE => __('enums.growth_pattern.espalier_tree'),
            self::POLLARDED_TREE => __('enums.growth_pattern.pollarded_tree'),
            self::STANDARD_TREE => __('enums.growth_pattern.standard_tree'),
            self::HALF_STANDARD_TREE => __('enums.growth_pattern.half_standard_tree'),
            self::COLUMNAR_TREE => __('enums.growth_pattern.columnar_tree'),
            self::ROOF_TREE => __('enums.growth_pattern.roof_tree'),
            self::DECIDUOUS_TREE => __('enums.growth_pattern.deciduous_tree'),
            self::MULTI_STEMMED_TREE => __('enums.growth_pattern.multi_stemmed_tree'),
            self::ESPALIER => __('enums.growth_pattern.espalier'),
            self::ROCK_PLANT => __('enums.growth_pattern.rock_plant'),
            self::AQUATIC_PLANT => __('enums.growth_pattern.aquatic_plant'),
            self::MARGINAL_PLANT => __('enums.growth_pattern.marginal_plant'),
            self::TOPIARY => __('enums.growth_pattern.topiary'),
            self::SPECIMEN => __('enums.growth_pattern.specimen'),
            self::HEDGE => __('enums.growth_pattern.hedge'),
            self::ORNAMENTAL_GRASS => __('enums.growth_pattern.ornamental_grass'),
            self::SUCCULENTS => __('enums.growth_pattern.succulents'),
            self::GROUNDCOVER => __('enums.growth_pattern.groundcover'),
            self::OXYGENATING_PLANT => __('enums.growth_pattern.oxygenating_plant'),
            self::FLOATING_PLANT => __('enums.growth_pattern.floating_plant'),
            self::MOSSES => __('enums.growth_pattern.mosses'),
            self::UNDERWATER_MOSSES_FEATHER_FORMS => __('enums.growth_pattern.underwater_mosses_feather_forms'),
        };
    }
}
