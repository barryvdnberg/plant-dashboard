<?php

namespace App\Filament\Schemas\Components;

use App\Filament\Schemas\Schema;

class InlineSchema extends Schema
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->inline();
    }
}
