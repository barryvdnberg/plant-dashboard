<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

trait HasLocalizedRecordTitle
{
    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record?->display_name;
    }
}
