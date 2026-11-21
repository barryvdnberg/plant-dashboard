<?php

namespace App\Filament\Support\Concerns;

use Closure;

/**
 * Trait CanDynamicallySpanColumns
 *
 * Provides functionality to dynamically span columns based on certain conditions.
 * Requires the CanSpanColumns trait to also be implemented
 */
trait CanDynamicallySpanColumns
{
    private const FULL_SPAN = ['default' => 'full'];

    public function columnSpanWhen(array | int | string | Closure | null $span, Closure $condition): static
    {
        $this->columnSpan(function ($component) use ($span, $condition) {
            if ($component->evaluate($condition)) {
                return $span;
            }
            return null;
        });
        
        return $this;
    }

    public function columnSpanFullWhen(Closure $condition): static
    {
        return $this->columnSpanWhen(self::FULL_SPAN, $condition);
    }

    public function columnSpanWhenFieldPresent(array | int | string | Closure | null $span, string $field, string $relativePath = ''): static
    {
        return $this->columnSpanWhen($span, function ($get) use ($field, $relativePath) {
            return key_exists($field, $get($relativePath));
        });
    }

    public function columnSpanFullWhenFieldPresent(string $field, string $relativePath = ''): static
    {
        return $this->columnSpanWhenFieldPresent(self::FULL_SPAN, $field, $relativePath);
    }

    public function columnSpanWhenFieldMissing(array | int | string | Closure | null $span, string $field, string $relativePath = ''): static
    {
        return $this->columnSpanWhen($span, function ($get) use ($field, $relativePath) {
            return ! key_exists($field, $get($relativePath));
        });
    }

    public function columnSpanFullWhenFieldMissing(string $field, string $relativePath = ''): static
    {
        return $this->columnSpanWhenFieldMissing(self::FULL_SPAN, $field, $relativePath);
    }

}