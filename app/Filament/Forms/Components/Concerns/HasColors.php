<?php

namespace App\Filament\Forms\Components\Concerns;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;

trait HasColors
{
    /**
     * @var array<string | Htmlable> | Arrayable | Closure
     */
    protected array | Arrayable | Closure $colors = [];

    /**
     * @param  array<string | Htmlable> | Arrayable | Closure $colors
     */
    public function colors(array | Arrayable | Closure $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * @return array<string | Htmlable>
     */
    public function getColors(): array
    {
        $colors = $this->evaluate($this->colors) ?? [];

        if ($colors instanceof Arrayable) {
            $colors = $colors->toArray();
        }

        return $colors;
    }

    public function hasDynamicColors(): bool
    {
        return $this->colors instanceof Closure;
    }
}
