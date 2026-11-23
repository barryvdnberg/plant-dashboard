<?php

namespace App\Filament\Forms\Components\Concerns;

use Closure;
use Filament\Support\Contracts\HasLabel as LabelInterface;
use Illuminate\Contracts\Support\Arrayable;
use UnitEnum;

trait HasLabels
{
    /**
     * @var array<string | array<string>> | Arrayable | string | Closure | null
     */
    protected array | Arrayable | string | Closure | null $labels = null;

    /**
     * @param  array<string | array<string>> | Arrayable | string | Closure | null  $labels
     */
    public function labels(array | Arrayable | string | Closure | null $labels): static
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @return array<string | array<string>>
     */
    public function getLabels(): array
    {
        $options = $this->evaluate($this->labels) ?? [];

        if (
            is_string($options) &&
            enum_exists($enum = $options)
        ) {
            if (is_a($enum, LabelInterface::class, allow_string: true)) {
                return array_reduce($enum::cases(), function (array $carry, LabelInterface&UnitEnum $case): array {
                    $carry[$case->value ?? $case->name] = $case->getLabel() ?? $case->name;

                    return $carry;
                }, []);
            }

            return array_reduce($enum::cases(), function (array $carry, UnitEnum $case): array {
                $carry[$case->value ?? $case->name] = $case->name;

                return $carry;
            }, []);
        }

        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        return $options;
    }

    public function hasLabels(): bool
    {
        return filled($this->labels);
    }

    public function hasDynamicLabels(): bool
    {
        return $this->labels instanceof Closure;
    }
}
