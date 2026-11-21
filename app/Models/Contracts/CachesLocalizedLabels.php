<?php

namespace App\Models\Contracts;

interface CachesLocalizedLabels
{
    public static function forgetLocalizedLabels(?int $languageId = null): void;
}
