<?php

namespace App\Filament\Schemas\Components\Concerns;

use Closure;

trait HasChildWithSingularRelationship
{
    protected ?string $relationship = null;

    protected bool | Closure $hasRelationship = false;

    protected string | Closure | null $relatedModel = null;

    public function relationship(string $name, bool | Closure $condition = true, string | Closure | null $relatedModel = null): static
    {
        $this->relationship = $name;
        $this->hasRelationship = $condition;
        $this->relatedModel = $relatedModel;
        
        return $this;
    }

    public function condition(bool | Closure $condition = true): static 
    {
        $this->hasRelationship = $condition;

        return $this;
    }

    public function getRelationshipName(): ?string
    {
        return $this->relationship;
    }

    public function hasRelationship(): bool
    {
        return ($this->relationship && $this->hasRelationship);
    }

    public function getRelatedModel(): string | Closure | null
    {
        return $this->relatedModel;
    }

}