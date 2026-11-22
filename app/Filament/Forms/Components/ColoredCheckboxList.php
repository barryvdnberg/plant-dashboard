<?php

namespace App\Filament\Forms\Components;

use App\Filament\Forms\Components\Concerns\HasColors;
use BackedEnum;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Support\Contracts\HasColor;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;
use UnitEnum;

use function Illuminate\Support\enum_value;

class ColoredCheckboxList extends CheckboxList
{
    use HasColors;

    protected string $view = 'filament.forms.components.checkbox-list';

    public function relationship(string|Closure|null $name = null, string|Closure|null $titleAttribute = null, ?Closure $modifyQueryUsing = null): static
    {
        parent::relationship($name, $titleAttribute, $modifyQueryUsing);
        // options set by the parent relationship method.
        $relationshipOptions = $this->options;

        // null falls back to the enum options supplied by HasOptions.
        return $this->options(static fn (self $component) => $component->resolveRelationship() instanceof HasMany
            ? null
            : $component->evaluate($relationshipOptions));
    }

    public function getEnum(): ?string
    {
        $enum = parent::getEnum();

        // as we don't ever tell the componennt what enum is used
        // we need to determine it based on the relationship's cast if available.

        if ($enum !== null || ! ($relationship = $this->resolveRelationship()) instanceof HasMany) {
            return $enum;
        }

        $cast = $relationship->getRelated()->getCasts()[$this->getHasManyValueColumn()] ?? null;

        return is_string($cast) && is_a($cast, BackedEnum::class, allow_string: true) ? $cast : null;
    }

    public function getRelationship(): ?BelongsToMany
    {
        // filaments getter is typed specifically for its many-to-many,
        // so we need to override it to not crash on HasMany relationships.
        // the method should still not be used to try to obtain the hasMany relationships.
        $relationship = $this->resolveRelationship();

        return $relationship instanceof BelongsToMany ? $relationship : null;
    }

    protected function resolveRelationship(): BelongsToMany|HasMany|null
    {
        $name = $this->getRelationshipName();

        if (blank($name) || ! $this->hasContainer()) {
            return null;
        }

        $model = $this->getModelInstance();
        $relationship = $model?->isRelation($name) ? $model->{$name}() : null;

        if (! $relationship instanceof BelongsToMany && ! $relationship instanceof HasMany) {
            throw new LogicException("The relationship [{$name}] on [{$this->getModel()}] must be a BelongsToMany or HasMany relationship.");
        }

        return $relationship;
    }

    protected function getHasManyValueColumn(): string
    {
        return $this->getRelationshipTitleAttribute()
            ?? throw new LogicException('A HasMany checkbox list requires a value column as the second argument to relationship().');
    }

    protected function scopeHasManyRelationship(HasMany $relationship): void
    {
        $this->evaluate($this->modifyRelationshipQueryUsing, ['query' => $relationship->getQuery()]);
    }

    public function fillStateFromRelationship(): void
    {
        $relationship = $this->resolveRelationship();

        if (! $relationship instanceof HasMany) {
            parent::fillStateFromRelationship();

            return;
        }

        $this->scopeHasManyRelationship($relationship);
        $column = $relationship->getRelated()->qualifyColumn($this->getHasManyValueColumn());

        $this->state($relationship->toBase()->pluck($column)->unique()->values()->all());
    }

    public function saveStateToRelationship(): void
    {
        $relationship = $this->resolveRelationship();

        if (! $relationship instanceof HasMany) {
            parent::saveStateToRelationship();

            return;
        }

        $this->scopeHasManyRelationship($relationship);
        $valueColumn = $this->getHasManyValueColumn();
        $parent = $relationship->getParent();
        $column = $relationship->getRelated()->qualifyColumn($valueColumn);
        $selected = collect($this->getState() ?? [])
            ->map(static fn ($value): string => (string) enum_value($value))
            ->unique()
            ->all();

        $parent->getConnection()->transaction(function () use ($parent, $relationship, $column, $valueColumn, $selected): void {
            // serialize changes to the selection for this parent.
            $parent->newModelQuery()->whereKey($parent->getKey())->lockForUpdate()->firstOrFail([$parent->getKeyName()]);

            $existing = $relationship->toBase()->pluck($column)
                ->map(static fn ($value): string => (string) $value)
                ->all();
            $removed = array_diff($existing, $selected);

            if ($removed !== []) {
                (clone $relationship)->whereIn($column, $removed)->delete();
            }

            foreach (array_diff($selected, $existing) as $value) {
                $relationship->create([$valueColumn => $value]);
            }
        });

        $parent->unsetRelation($this->getRelationshipName());
    }

    /**
     * @param  array-key  $value
     */
    public function getLabelColor($value): ?string
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
            $colors = array_reduce($enum::cases(), function (array $carry, HasColor&UnitEnum $case): array {
                if (filled($color = $case->getColor())) {
                    $carry[$case->value ?? $case->name] = $color;
                }

                return $carry;
            }, []);
        }

        return $colors;
    }
}
