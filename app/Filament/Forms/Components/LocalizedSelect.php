<?php

namespace App\Filament\Forms\Components;

/** Compatibility name for a searchable, localized relationship select. */
class LocalizedSelect extends RelationshipSelect
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->searchable();
    }
}
