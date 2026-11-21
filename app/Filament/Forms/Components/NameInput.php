<?php

namespace App\Filament\Forms\Components;

use App\Filament\Support\Concerns\CanDynamicallySpanColumns;
use Filament\Forms\Components\TextInput;

class NameInput extends TextInput
{
    use CanDynamicallySpanColumns;

    public static function make(?string $name = null): static
    {
        if (! $name) {
            $name = 'name';
        }
        /** @var static $field */
        $field = parent::make($name);

        return $field
            ->required()
            ->label(__('attributes.Name'));
    }
}
