<?php

namespace App\Filament\Forms\Components\Concerns;

use Closure;

trait RelationshipCanHaveFormSchema
{
    protected Closure | bool $enableCreateOptionForm = false;

    protected Closure | bool $enableEditOptionForm = false;

    /** @var string|null $formSchemaClass */
    protected ?string $formSchemaClass = null;

    protected ?array $formSchema = null;

    public function enableCreateOptionForm(Closure | bool $condition = true): static
    {
        $this->enableCreateOptionForm = $condition;

        return $this;
    }

    public function disableCreateOptionForm(): static
    {
        return $this->enableCreateOptionForm(false);
    }

    public function enableEditOptionForm(Closure | bool $condition = true): static
    {
        $this->enableEditOptionForm = $condition;

        return $this;
    }

    public function disableEditOptionForm(): static
    {
        return $this->enableEditOptionForm(false);
    }
    
    public function enableManageOptionForm(Closure | bool $condition = true): static
    {
        $this->enableCreateOptionForm($condition);
        $this->enableEditOptionForm($condition);

        return $this;
    }

    public function disableManageOptionForm(): static
    {
        $this->disableCreateOptionForm();
        $this->disableEditOptionForm();

        return $this;
    }

    public function formSchemaClass(string $class): static
    {
        // check if the class exists and has a static form() method
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Form schema class {$class} does not exist.");
        }

        if (!method_exists($class, 'form')) {
            throw new \InvalidArgumentException("Form schema class {$class} must have a static form() method.");
        }

        $this->formSchemaClass = $class;
        
        return $this;
    }

    public function formSchema(array $schema): static
    {
        $this->formSchema = $schema;

        return $this;
    }

    // Overrides Filament\Forms\Components\Select hasCreateOptionActionFormSchema() method
    public function hasCreateOptionActionFormSchema(): bool
    {
        return $this->evaluate($this->enableCreateOptionForm) && 
            class_exists($this->getFormSchemaClass());
    }

    // Overrides Filament\Forms\Components\Select hasEditOptionActionFormSchema() method
    public function hasEditOptionActionFormSchema(): bool
    {
        return $this->evaluate($this->enableEditOptionForm) && 
            class_exists($this->getFormSchemaClass()) && 
            !$this->isMultiple();
    }

    protected function getFormSchema(): array
    {
        if ($this->formSchema) {
            return $this->formSchema;
        }

        return $this->getFormSchemaClass()::form();
    }

    protected function getFormSchemaClass(): string 
    {
        if ($this->formSchemaClass) {
            return $this->formSchemaClass;
        }

        $modelName = class_basename($this->getRelationship()->getRelated());

        return "App\\Filament\\Forms\\Builder\\{$modelName}Form";
    }

    protected function configureManageOptionForm()
    {
        $this->configureCreateOptionForm();
        $this->configureEditOptionForm();
    }

    protected function configureCreateOptionForm(): void
    {
        $this->createOptionForm(fn () => $this->getFormSchema());
        $this->createOptionAction(fn (\Filament\Actions\Action $action) => $action
            ->authorize(static fn ($component) => \Illuminate\Support\Facades\Gate::allows('create', $component->getRelationship()->getRelated()::class)));
        $this->enableCreateOptionForm();
    }

    protected function configureEditOptionForm(): void
    {
        $this->editOptionForm(fn () => $this->getFormSchema());
        $this->editOptionAction(fn (\Filament\Actions\Action $action) => $action
            ->authorize(static fn ($component) => ($record = $component->getSelectedRecord()) && \Illuminate\Support\Facades\Gate::allows('update', $record)));
        $this->enableEditOptionForm();
    }
}
