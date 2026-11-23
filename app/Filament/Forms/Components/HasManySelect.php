<?php

namespace App\Filament\Forms\Components;

use App\Filament\Forms\Components\Concerns\RelationshipCanHaveModelLabel;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;

class HasManySelect extends Select
{
    use RelationshipCanHaveModelLabel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->multiple();
        $this->configureLabel();
    }

    public function table(string $name, string $valueColumn = 'id'): static 
    {
        $this->relationship = $name;
        $this->relationshipTitleAttribute = $valueColumn;

        $this->afterStateHydrated(static function (HasManySelect $component, ?Model $record): void {
            if (!$record) {
                return;
            }

            $relation = $component->getRelationship();

            if (! $relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                throw new \Exception("Relation {$component->getRelationshipName()} is not a HasMany relation.");
            }

            $component->state(
                $relation
                    ->getResults()
                    ->pluck($component->getRelationshipTitleAttribute())
                    ->map(static fn ($key): string => strval($key))
                    ->all()
            );
        });

        $this->saveRelationshipsUsing(static function (HasManySelect $component, $state): void
        {
            $relation = $component->getRelationship();

            if (! $relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                throw new \Exception("Relation {$component->getRelationshipName()} is not a HasMany relation.");
            }

            $relation->delete();

            foreach ($state ?? [] as $value) {
                $relation->create([
                    $component->getRelationshipTitleAttribute() => $value,
                ]);
            }
        });

        return $this;
    }

}
