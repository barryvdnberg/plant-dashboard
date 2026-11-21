<?php

namespace App\Models\Concerns;

use App\Models\Contracts\Localization;
use App\Models\Contracts\ParentRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Coordinating trait for models that are used as query roots.
 *
 * Apply this trait to any model you query directly and want full localization support on
 * (e.g. Plant, Cultivar, Species). Models that only need their own translations can use
 * withLocalization() and addLocalizationCoalesce() from HasLocalization directly.
 *
 * Usage:
 *   Plant::query()->withLocalizedData(depth: 2)->addParentSelect()->addLocalizationCoalesce()->get();
 *   Cultivar::query()->withLocalizedData()->addLocalizationCoalesce()->get();
 *
 * @mixin Model
 *
 * @phpstan-require-extends Model
 *
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait HasLocalizedQuery
{
    /**
     * Set up all localization-related joins for this model in one call.
     *
     * Automatically applies, based on what the model implements/uses:
     * - Self-localization join            (when the model implements Localization via HasLocalization)
     * - Parent chain + localization joins (when the model implements ParentRelationship via HasParent)
     * - Configured BelongsTo joins        (when the model uses HasBelongsToProperties)
     *
     * Follow with addLocalizationCoalesce() or addLocalizationSelect() for translation projections,
     * and addParentSelect() for parent name columns.
     *
     * @param  int  $depth  Number of parent generations to join and localise (HasParent models only).
     * @param  string|null  $locale  Locale to filter localizations by. Defaults to the application locale.
     */
    public function scopeWithLocalizedData(Builder $query, int $depth = 1, ?string $locale = null): Builder
    {
        // Keep model accessors locale-safe and avoid per-row translation queries.
        $model = $this;
        $path = '';
        for ($level = 0; $level <= $depth; $level++) {
            if ($model instanceof Localization) {
                $query->with($path.'localizations');
            }
            if (! $model instanceof ParentRelationship) {
                break;
            }
            $path .= 'parent.';
            $model = $model->parent()->getRelated();
        }

        if ($this instanceof Localization) {
            $this->scopeWithLocalization($query, null, $locale);
        }

        if ($this instanceof ParentRelationship) {
            $this->scopeWithLocalizedParent($query, $depth, $locale);
        }

        if (method_exists($this, 'applyBelongsToPropertyJoins')) {
            $this->applyBelongsToPropertyJoins($query, $locale);
        }

        return $query;
    }
}
