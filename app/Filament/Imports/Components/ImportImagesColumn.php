<?php

namespace App\Filament\Imports\Components;

use Filament\Actions\Imports\ImportColumn;

class ImportImagesColumn extends ImportColumn
{
    public static function make(?string $name = null): static
    {
        /** @var static $field */
        $field = parent::make($name);

        return $field
            ->multiple(',')
            ->nestedRecursiveRules([
                new \App\Rules\ImageDirectory
            ])
            ->example("uploads/images/01K.png, uploads/images/02K.png");
    }
}
