<?php

namespace App\Filament\Forms\Components\Concerns;

use App\Models\Concerns\HasModelLabel;
use Filament\Forms\Components\Field;

/**
 * Trait to add model label functionality to relationship form components.
 */
trait RelationshipCanHaveModelLabel
{
    /**
     * Determine if the relationship model uses the HasModelLabel concern.
     */
    protected function hasModelLabel(): bool
    {
        return in_array(HasModelLabel::class, class_uses($this->getRelationship()->getModel()::class));
    }

    /**
     * Get the model label of the relationship model, or return the provided fallback.
     * 
     * @param string|null $fallback
     * @return string|null
     */
    protected function getModelLabel(?string $fallback = null): ?string
    {
        if ($this->hasModelLabel()) {
            return $this->getRelationship()->getModel()::getModelLabel();
        }
        return $fallback;
    }

    /**
     * Configure the label to use the model label of the relationship model, if it has one.
     */
    protected function configureLabel(): void
    {
        $this->label(static function (Field $component) {
            return $component->getModelLabel();
        });
    }
    
}
