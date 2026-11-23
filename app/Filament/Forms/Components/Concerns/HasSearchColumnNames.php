<?php

namespace App\Filament\Forms\Components\Concerns;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Overrides the base Select getSearchColumns method to use custom column names.
 * Also allows dynamic evaluation of column names using a Closure.
 */
trait HasSearchColumnNames
{
    protected array | Arrayable | string | Closure | null $searchColumnNames;
    
    /**
     * @param  array<string> | string | Closure | null  $columnNames
     */
    public function searchColumnNames(array | string | Closure | null $columnNames): static
    {
        $this->searchColumnNames = $columnNames;

        return $this;
    }

    /**
     * @return array<string> | null
     */
    public function getSearchColumns(): ?array
    {
        $columns = $this->evaluate($this->searchColumnNames);

        if ($this->hasRelationship() && (filled($relationshipTitleAttribute = $this->getRelationshipTitleAttribute()))) {
            $columns ??= [$relationshipTitleAttribute];
        }

        if (is_string($columns)) {
            return [$columns];
        }

        if ($columns instanceof Arrayable) {
            return $columns->toArray();
        }

        return $columns;
    }
}
