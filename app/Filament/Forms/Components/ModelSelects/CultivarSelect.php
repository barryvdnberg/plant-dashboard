<?php

namespace App\Filament\Forms\Components\ModelSelects;

use App\Models\Cultivar;
use Filament\Forms\Components\Select;

class CultivarSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->getSearchResultsUsing(function (string $search) {
                return Cultivar::query()
                    ->withLocalizedData(depth: 1)
                    ->where(function ($q) use ($search) {
                        $q->where('cultivars.name', 'like', "%{$search}%")
                            ->orWhere('_localizables.value', 'like', "%{$search}%")
                            ->orWhere('_localizables.synonyms', 'like', "%{$search}%")
                            ->orWhere('_parent.name', 'like', "%{$search}%")
                            ->orWhere('_parent_localizables.value', 'like', "%{$search}%")
                            ->orWhere('_parent_localizables.synonyms', 'like', "%{$search}%");
                    })
                    ->limit(50)
                    ->select('cultivars.*')
                    ->get()
                    ->mapWithKeys(fn (Cultivar $cultivar) => [$cultivar->id => $cultivar->display_name])
                    ->all();
            })
            ->getOptionLabelUsing(function ($value): ?string {
                return Cultivar::query()
                    ->withLocalizedData(depth: 1)
                    ->where('cultivars.id', $value)
                    ->select('cultivars.*')
                    ->first()?->display_name;
            });
    }
}
