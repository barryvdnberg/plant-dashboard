<?php

namespace App\Filament\Tables\Columns;

use App\Models\Color;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Collection;

class ColorTagsColumn extends Column
{
    protected string $view = 'filament.tables.columns.color-tags-column';

    protected int $limit = 5;

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getState(): Collection
    {
        $state = parent::getState();

        return $this->mutateArrayState($state);
    }

    protected function mutateArrayState(mixed $state): Collection
    {
        if (blank($state)) {
            return collect();
        }
        
        $colors = collect($state);
        $limit = $this->getLimit();

        // Detect if all items are actually a Color model
        $allAreModels = $colors->every(fn ($item) => $item instanceof Color);

        if ($allAreModels) {
            $localizedColorLabels = Color::localizedMap();

            $ids = $colors->pluck('id')->filter()->unique();

            if ($ids->isNotEmpty()) {

                $colors->transform(function (Color $color) use ($localizedColorLabels) {
                    if ($localizedColorLabels->has($color->id)) {
                        $color->translation = $localizedColorLabels->get($color->id);
                    }
                    else {
                        $color->translation = $color->name;
                    }
                    return $color;
                });

            }
            return $colors->take($limit);
        }

        // return an empty collection if results are mixed
        return collect();
    }
}
