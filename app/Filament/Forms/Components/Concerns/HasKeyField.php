<?php

namespace App\Filament\Forms\Components\Concerns;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

trait HasKeyField
{
    protected Field | Closure | null $keyField = null;

    protected string $keyFieldName = 'key';

    protected ?Closure $modifyKeyFieldUsing = null;
    
    protected bool | Closure $isKeyFieldRequired = true;


    public function keyField(Field | Closure | null $field): static
    {
        $this->keyField = $field;

        return $this;
    }
    
    public function keyFieldName(string $name): static
    {
        $this->keyFieldName = $name;

        return $this;
    }

    public function modifyKeyFieldUsing(Closure $callback): static
    {
        $this->modifyKeyFieldUsing = $callback;

        return $this;
    }

    public function keyFieldRequired(bool | Closure $condition = true): static
    {
        $this->isKeyFieldRequired = $condition;

        return $this;
    }

    public function getKeyField(): Field
    {
        /** @var Field|null $field */
        $field = $this->evaluate($this->keyField) ?? $this->keyField ?? null;

        if (empty($field)) {
            if ($this->options) {
                $field = Select::make($this->keyFieldName);

                if ($this->hasDisabledOptions()) {
                    $field->options($this->getEnabledOptions());
                }
                else {
                    $field->options($this->getOptions());
                }
                $field->getOptionLabelUsing(fn ($value) => $this->getOptions()[$value] ?? null);
            }
            else {
                $field = TextInput::make($this->keyFieldName);
            }
        }

        if ($this->modifyKeyFieldUsing) {
            $this->evaluate($this->modifyKeyFieldUsing, [
                'component' => $this,
                'field' => $field,
            ]);
        }

        return $field->required($this->isKeyFieldRequired());
    }

    public function getHiddenKeyField(): Field
    {
        return Hidden::make($this->getKeyFieldName())->required($this->isKeyFieldRequired());
    }

    public function getKeyFieldName(): string
    {
        if ($this->keyField === null)
            return $this->keyFieldName;

        return $this->getKeyField()->getName();
    }

    public function isKeyFieldRequired(): bool
    {
        return (bool) $this->evaluate($this->isKeyFieldRequired);
    }
}