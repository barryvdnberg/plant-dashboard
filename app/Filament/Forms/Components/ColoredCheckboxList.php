<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Forms\Components\Concerns\HasColors;
use Filament\Support\Contracts\HasColor;
use UnitEnum;

class ColoredCheckboxList extends CheckboxList
{
    use HasColors;

    protected string $view = 'filament.forms.components.checkbox-list';

    /**
     * @param  array-key  $value
     */
    public function getLabelColor($value): string | null
    {
        return $this->getLabelColors()[$value] ?? null;
    }

    /**
     * @return array<string | Htmlable>
     */
    public function getLabelColors(): array
    {
        $colors = $this->getColors();

        if (
            blank($colors) &&
            filled($enum = $this->getEnum()) &&
            is_a($enum, HasColor::class, allow_string: true)
        ) {
            $colors = array_reduce($enum::cases(), function (array $carry, HasColor & UnitEnum $case): array {
                if (filled($color = $case->getColor())) {
                    $carry[$case->value ?? $case->name] = $color;
                }

                return $carry;
            }, []);
        }

        return $colors;
    }
}
