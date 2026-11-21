<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

class NumberInput extends TextInput
{
    public static function make(?string $name = null): static
    {
        /** @var static $field */
        $field = parent::make($name);

        return $field
            ->numeric()
            ->inputMode('decimal')
            ->maxLength(null);
    }
}
