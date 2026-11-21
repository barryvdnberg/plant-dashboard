<?php

namespace App\Filament\Forms\Resolvers;

use App\Filament\Forms\Components\NameInput;
use Closure;
use Filament\Forms\Components\Field;
use ReflectionFunction;

class RelationFormResolver
{
    private bool $includeRelationshipFields = true;
    private array $relationshipFields;

    private Field $nameField;

    private ?Closure $modifyFieldsIfRelationshipIncludedUsing = null;
    private ?Closure $modifyFieldsIfRelationshipExcludedUsing = null;

    public function __construct(
        Field|array $relationshipFields,
        string $nameFieldName = 'name',
    ) {
        if (! is_array($relationshipFields)) {
            $relationshipFields = [$relationshipFields];
        } else {
            foreach ($relationshipFields as $field) {
                if (! $field instanceof Field) {
                    throw new \InvalidArgumentException('All elements of $relationshipFields must be instances of Field.');
                }
            }
        }

        $this->relationshipFields = $relationshipFields;
        $this->nameField = NameInput::make($nameFieldName);
    }

    public static function make(
        Field|array $relationshipFields,
        string $nameFieldName = 'name',
    ): static {
        return new static($relationshipFields, $nameFieldName);
    }

    public function includeRelationshipFields(bool $include = true): static
    {
        $this->includeRelationshipFields = $include;

        return $this;
    }

    public function modifyFieldsIfRelationshipIncludedUsing(Closure $modifyFieldsIfRelationshipIncludedUsing): static
    {
        $this->modifyFieldsIfRelationshipIncludedUsing = $modifyFieldsIfRelationshipIncludedUsing;

        return $this;
    }

    public function modifyFieldsIfRelationshipExcludedUsing(Closure $modifyFieldsIfRelationshipExcludedUsing): static
    {
        $this->modifyFieldsIfRelationshipExcludedUsing = $modifyFieldsIfRelationshipExcludedUsing;

        return $this;
    }

    /**
     * Calls a closure with only the parameters it declares, matched by name.
     *
     * Available injection slots:
     *  - `fields` / `relationshipFields`  → the relationship fields array (pass by-reference with `array &$fields` to mutate)
     *  - `nameField` / `nameInput`        → the NameInput instance
     */
    private function evaluateClosure(Closure $closure): void
    {
        $ref = new ReflectionFunction($closure);
        $args = [];

        foreach ($ref->getParameters() as $param) {
            if (in_array($param->getName(), ['fields', 'relationshipFields'])) {
                // Preserve reference so `array &$fields` in the closure mutates the property.
                $args[] = &$this->relationshipFields;
            } elseif (in_array($param->getName(), ['nameField', 'nameInput'])) {
                $args[] = $this->nameField;
            } elseif ($param->isOptional()) {
                $args[] = $param->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        call_user_func_array($closure, $args);
    }

    public function resolve(): array
    {
        if ($this->includeRelationshipFields) {
            if ($this->modifyFieldsIfRelationshipIncludedUsing) {
                $this->evaluateClosure($this->modifyFieldsIfRelationshipIncludedUsing);
            }

            return [$this->nameField, ...$this->relationshipFields];
        }

        if ($this->modifyFieldsIfRelationshipExcludedUsing) {
            $this->evaluateClosure($this->modifyFieldsIfRelationshipExcludedUsing);
        } else {
            $this->nameField->columnSpanFull();
        }

        return [$this->nameField];
    }

    public function __invoke(): array
    {
        return $this->resolve();
    }
}
