<?php

namespace App\Models\Concerns;

use App\Models\Contracts\ParentRelationship;

trait HasScientificName
{
    protected function scientificNameDepth(): int
    {
        return 0;
    }

    public function getScientificNameAttribute(): string
    {
        if ($this->scientificNameDepth() > 0 && $this instanceof ParentRelationship) {
            return trim(($this->parent?->scientific_name ?? '').' '.$this->name);
        }

        return (string) $this->name;
    }
}
