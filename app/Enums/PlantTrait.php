<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Illuminate\Contracts\Support\Htmlable;

enum PlantTrait: int implements HasLabel, HasDescription, HasColor
{
    case DISEASE_SUSCEPTIBLE = 1;
    case TOXIC_TO_PETS = 2;
    case SEA_WIND_TOLERANT = 3;
    CASE SEA_WIND_VULNERABLE = 4;
    case HAS_SCENT = 5;
    case ATTRACTS_SNAILS = 6;
    case REPELS_SNAILS = 7;
    case ATTRACTS_INSECTS = 8;
    case ATTRACTS_BIRDS = 9;
    case IS_AQUATIC = 10;
    case NITROGEN_FIXING = 11;
    case NON_EDIBLE_FRUIT = 12;

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::DISEASE_SUSCEPTIBLE => __('enums.plant_trait.disease_susceptible'),
            self::TOXIC_TO_PETS => __('enums.plant_trait.toxic_to_pets'),
            self::SEA_WIND_TOLERANT => __('enums.plant_trait.sea_wind_tolerant'),
            self::SEA_WIND_VULNERABLE => __('enums.plant_trait.sea_wind_vulnerable'),
            self::HAS_SCENT => __('enums.plant_trait.has_scent'),
            self::ATTRACTS_SNAILS => __('enums.plant_trait.attracts_snails'),
            self::REPELS_SNAILS => __('enums.plant_trait.repels_snails'),
            self::ATTRACTS_INSECTS => __('enums.plant_trait.attracts_insects'),
            self::ATTRACTS_BIRDS => __('enums.plant_trait.attracts_birds'),
            self::IS_AQUATIC => __('enums.plant_trait.is_aquatic'),
            self::NITROGEN_FIXING => __('enums.plant_trait.nitrogen_fixing'),
            self::NON_EDIBLE_FRUIT => __('enums.plant_trait.non_edible_fruit'),
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::DISEASE_SUSCEPTIBLE => __('enums.plant_trait.disease_susceptible_description'),
            self::TOXIC_TO_PETS => __('enums.plant_trait.toxic_to_pets_description'),
            self::SEA_WIND_TOLERANT => __('enums.plant_trait.sea_wind_tolerant_description'),
            self::SEA_WIND_VULNERABLE => __('enums.plant_trait.sea_wind_vulnerable_description'),
            self::HAS_SCENT => __('enums.plant_trait.has_scent_description'),
            self::ATTRACTS_SNAILS => __('enums.plant_trait.attracts_snails_description'),
            self::REPELS_SNAILS => __('enums.plant_trait.repels_snails_description'),
            self::ATTRACTS_INSECTS => __('enums.plant_trait.attracts_insects_description'),
            self::ATTRACTS_BIRDS => __('enums.plant_trait.attracts_birds_description'),
            self::IS_AQUATIC => __('enums.plant_trait.is_aquatic_description'),
            self::NITROGEN_FIXING => __('enums.plant_trait.nitrogen_fixing_description'),
            self::NON_EDIBLE_FRUIT => __('enums.plant_trait.non_edible_fruit_description'),
        };
    }

    public function getColor(): string|array|null
    {
        // only support green and red (positive / negative)
        return match ($this) {
            self::DISEASE_SUSCEPTIBLE => 'danger',
            self::TOXIC_TO_PETS => 'danger',
            self::SEA_WIND_TOLERANT => 'success',
            self::SEA_WIND_VULNERABLE => 'danger',
            self::HAS_SCENT => 'success',
            self::ATTRACTS_SNAILS => 'danger',
            self::REPELS_SNAILS => 'success',
            self::ATTRACTS_INSECTS => 'success',
            self::ATTRACTS_BIRDS => 'success',
            self::IS_AQUATIC => 'success',
            self::NITROGEN_FIXING => 'success',
            self::NON_EDIBLE_FRUIT => 'danger',
        };
    }
}
