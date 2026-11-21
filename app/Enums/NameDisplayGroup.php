<?php

namespace App\Enums;

use App\Models\Cultivar;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\Species;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Model;

enum NameDisplayGroup: string implements HasLabel
{
    case Families = 'families';
    case Genera = 'genera';
    case Species = 'species';
    case Plants = 'plants';
    case Cultivars = 'cultivars';

    public function getLabel(): string
    {
        return __('preferences.groups.'.$this->value);
    }

    public static function forModel(Model $model): ?self
    {
        return match (true) {
            $model instanceof Family => self::Families,
            $model instanceof Genus => self::Genera,
            $model instanceof Species => self::Species,
            $model instanceof Plant => self::Plants,
            $model instanceof Cultivar => self::Cultivars,
            default => null,
        };
    }
}
