<?php

namespace App\Filament\Forms\Components\ModelSelects;

use App\Models\Plant;
use Closure;
use Filament\Forms\Components\Select;

class PlantSelect extends Select
{
    // store the extra where query here
    protected ?Closure $modifyQueryUsing = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->getSearchResultsUsing(function (string $search, Select $component) {
                $query = Plant::query()
                    ->withLocalizedData(depth: 0);

                if ($this->modifyQueryUsing) {
                    $query = $component->evaluate($this->modifyQueryUsing, [
                        'query' => $query,
                        'search' => $search,
                    ]) ?? $query;
                }

                return $query
                    ->where(function ($q) use ($search) {
                        $q->where('plants.name', 'like', "%{$search}%")
                            ->orWhere('_localizables.value', 'like', "%$search%")
                            ->orWhere('_localizables.synonyms', 'like', "%$search%");
                    })
                    ->limit(50)
                    ->select('plants.*')
                    ->get()
                    ->mapWithKeys(fn (Plant $plant) => [$plant->id => $plant->display_name])
                    ->all();
            })
            // essential for getting the label of the selected option if preloaded or submitting
            ->getOptionLabelUsing(function ($value): ?string {
                return Plant::query()
                    ->withLocalizedData(depth: 0)
                    ->where('plants.id', $value)
                    ->select('plants.*')
                    ->first()?->display_name;
            });
    }

    public function modifyQueryUsing(?Closure $modifyQueryUsing): static
    {
        $this->modifyQueryUsing = $modifyQueryUsing;

        return $this;
    }
}
