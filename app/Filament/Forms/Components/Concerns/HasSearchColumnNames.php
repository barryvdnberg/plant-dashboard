<?php

namespace App\Filament\Forms\Components\Concerns;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

// overide the base Select getSearchColumns method to use custom column names
trait HasSearchColumnNames
{
    protected array | Arrayable | string | Closure | null $searchColumnNames;
    
    public function searchColumnNames(array | string | Closure | null $columnNames): static
    {
        $this->searchColumnNames = $columnNames;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getSearchColumns(): ?array
    {
        // overides the base Select getSearchColumns method to use custom column names
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
