<?php

namespace App\Support\Contracts;

use Illuminate\Contracts\Support\Htmlable;

interface HasShortLabel
{
    public function getShortLabel(): string | Htmlable | null;
}
