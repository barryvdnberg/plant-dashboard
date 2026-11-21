<?php

namespace App\Filament\Forms\Components\Concerns;

use App\Models\Contracts\Localization;
use App\Models\Contracts\ParentRelationship;

/**
 * Trait to determine if the relationship model has localization capabilities.
 */
trait RelationshipCanHaveLocalization
{
    protected function relationshipHasLocalization(): bool
    {
        $model = $this->getRelationship()->getRelated();

        return $model instanceof Localization;
    }

    /**
     * Returns true when the related model's immediate parent implements the Localization contract,
     * meaning parent-level translated columns (`_parent_translation`) will be available after
     * scopeWithLocalizedParent is applied to the query.
     */
    protected function relationshipParentHasLocalization(): bool
    {
        $model = $this->getRelationship()->getRelated();

        if (!$model instanceof ParentRelationship) {
            return false;
        }

        return $model->parent()->getModel() instanceof Localization;
    }
}
