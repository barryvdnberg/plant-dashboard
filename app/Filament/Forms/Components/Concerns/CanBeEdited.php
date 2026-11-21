<?php

namespace App\Filament\Forms\Components\Concerns;

use Closure;

trait CanBeEdited
{
    protected bool | Closure $isEditable = true;

    public function editable(bool | Closure $condition = true): static
    {
        $this->isEditable = $condition;

        return $this;
    }

    public function isEditable(): bool
    {
        if ($this->isDisabled()) {
            return false;
        }

        return (bool) $this->evaluate($this->isEditable);
    }
}
