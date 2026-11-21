<?php

namespace App\Filament\Forms\Components;

use App\Filament\Forms\Components\Concerns\HasColors;
use Filament\Forms\Components\Field;
use Filament\Support\Enums\Size;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Contracts\HasShortLabel;
use Filament\Support\Contracts\HasLabel;
use Closure;
use UnitEnum;
use LogicException;
use Illuminate\Database\Eloquent\Model;

class MatrixField extends Field
{
    use HasColors;

    protected string $view = 'filament.forms.components.matrix-field';

    protected int $buttonColumns = 12;

    protected int $buttonRows = 1;

    protected array | Arrayable | string | Closure $columnLabels = [];

    protected array | Arrayable | string | Closure $rowLabels = [];

    protected Size $size = Size::Medium;
    
    protected string | Closure | null $relationshipTitleAttribute = null;

    protected string | Closure | null $relationship = null;

    public static function make(?string $name = null): static
    {
        $object = parent::make($name);

        $object->columnSpanFull();

        return $object;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->colors([
            0 => 'fi-color-m0',
            1 => 'fi-color-m1',
            2 => 'fi-color-m2',
        ]);

        $this->rules([
            fn (MatrixField $component): Closure => function (string $attribute, mixed $value, Closure $fail) use ($component): void {
                $size = $component->getButtonColumns() * $component->getButtonRows();

                if (! is_array($value) || ! array_is_list($value) || count($value) !== $size) {
                    $fail('The :attribute must contain exactly '.$size.' values.');
                    return;
                }

                foreach ($value as $cell) {
                    if (! in_array($cell, [0, 1, 2, '0', '1', '2'], true)) {
                        $fail('The :attribute contains an invalid intensity.');
                        return;
                    }
                }
            },
        ]);

        $this->afterStateHydrated(function (MatrixField $component, ?array $state): void {
            $component->state(
                $this->normalizeState($state)
            );
        });

        $this->dehydrateStateUsing(function (?array $state) {
            return collect($state ?? [])->map(fn ($v) => (int) $v)->values()->all();
        });
    }

    public function relationship(string | Closure | null $name = null, string | Closure | null $titleAttribute = null): static
    {
        $this->relationship = $name ?? $this->getName();
        $this->relationshipTitleAttribute = $titleAttribute;

        $this->afterStateHydrated(function (MatrixField $component, ?Model $record) {

            $relationship = $this->getRelationship($record);
            
            if (! $relationship) {
                $component->state($this->normalizeState([]));
                return;
            }

            $values = $relationship->orderBy('position')->pluck('value')->toArray();


            $component->state(
                $this->normalizeState($values)
            );
        });

        $this->saveRelationshipsUsing(function (?array $state, Model $record) {

            $relationship = $this->getRelationship($record);

            if (! $relationship) {
                return;
            }

            $state = $this->normalizeState($state);

            $relationship->delete();

            foreach ($state as $position => $value) {
                $relationship->create([
                    'position' => $position + 1,
                    'value' => $value,
                ]);
            }
        });

        return $this;
    }

    public function getRelationship(?Model $record): HasMany | null
    {
        if (! $record) {
            return null;
        }
        if (! $this->hasRelationship()) {
            return null;
        }

        $relationshipName = $this->getRelationshipName();

        foreach (explode('.', $relationshipName) as $nestedRelationshipName) {
            if ($record->hasAttribute($nestedRelationshipName)) {
                $relationship = null;

                break;
            }

            if (! $record->isRelation($nestedRelationshipName)) {
                $relationship = null;

                break;
            }

            $relationship = $record->{$nestedRelationshipName}();
            $record = $relationship->getRelated();
        }

        if (! $relationship) {
            throw new LogicException("The relationship [{$relationshipName}] does not exist on the model [{$this->getModel()}].");
        }

        return $relationship;
    }

    
    public function hasRelationship(): bool
    {
        return filled($this->getRelationshipName());
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationship);
    }

    public function layout(int $columns, int $rows): static
    {
        return $this->buttonColumns($columns)->buttonRows($rows);
    }

    public function buttonColumns(int $columns): static
    {
        $this->buttonColumns = $columns;

        return $this;
    }

    public function buttonRows(int $rows): static
    {
        $this->buttonRows = $rows;

        return $this;
    }

    public function columnLabels(array | Arrayable | string $labels): static
    {
        $this->columnLabels = $labels;
        return $this;
    }

    public function rowLabels(array | Arrayable | string | Closure $labels): static
    {
        $this->rowLabels = $labels;

        return $this;
    }

    public function size(Size $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getButtonColumns(): int
    {
        return $this->buttonColumns;
    }

    public function getButtonRows(): int
    {
        return $this->buttonRows;
    }

    public function getColumnLabels(): array
    {
        return $this->getLabels($this->columnLabels);
    }

    public function getRowLabels(): array
    {
        return $this->getLabels($this->rowLabels);
    }

    private function getLabels(array | Arrayable | string | Closure $labels): array
    {
        $labels = $this->evaluate($labels) ?? [];

        if (
            is_string($labels) &&
            enum_exists($enum = $labels)
        ) {
            if (is_a($enum, HasShortLabel::class, allow_string: true)) {
                return array_reduce($enum::cases(), function (array $carry, HasShortLabel & UnitEnum $case): array {
                    $carry[$case->value ?? $case->name] = $case->getShortLabel() ?? $case->name;

                    return $carry;
                }, []);
            }

            if (is_a($enum, HasLabel::class, allow_string: true)) {
                return array_reduce($enum::cases(), function (array $carry, HasLabel & UnitEnum $case): array {
                    $carry[$case->value ?? $case->name] = $case->getLabel() ?? $case->name;

                    return $carry;
                }, []);
            }

            return array_reduce($enum::cases(), function (array $carry, UnitEnum $case): array {
                $carry[$case->value ?? $case->name] = $case->name;

                return $carry;
            }, []);
        }

        if ($labels instanceof Arrayable) {
            $labels = $labels->toArray();
        }

        return $labels;
    }

    public function getSize(): Size
    {
        return $this->size;
    }

    private function normalizeState(?array $state)
    {
        $totalButtons = $this->getButtonColumns() * $this->getButtonRows();

        return collect ($state ?? [])
            ->map(fn ($value) => (int) $value)
            ->pad($totalButtons, 0)
            ->take($totalButtons)
            ->values()
            ->all();
    }
}
