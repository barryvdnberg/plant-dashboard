<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NameDisplay: string implements HasLabel
{
    case Scientific = 'scientific';
    case Translated = 'translated';

    public function getLabel(): string
    {
        return __('preferences.display.'.$this->value);
    }
}
