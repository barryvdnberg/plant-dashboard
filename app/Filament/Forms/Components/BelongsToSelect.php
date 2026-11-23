<?php

namespace App\Filament\Forms\Components;

use App\Models\Contracts\Localization;
use App\Models\Contracts\ParentRelationship;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use LogicException;

class BelongsToSelect extends Select
{
    use Concerns\HasSearchColumnNames;
    use Concerns\RelationshipCanHaveFormSchema;
    use Concerns\RelationshipCanHaveLocalization;
    use Concerns\RelationshipCanHaveModelLabel;

    protected bool $withParent = false;

    protected bool $parentSearchable = false;

    protected string $parentTitleAttributeName = 'name';

    public function withParent(bool $searchable = true): static
    {
        $this->withParent = true;
        $this->parentSearchable($searchable);

        return $this;
    }

    public function parentSearchable(bool $searchable = true): static
    {
        $this->parentSearchable = $searchable;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (str_ends_with($this->getName(), '_id')) {
            $this->relationship();
        }

        $this->configureLabel();
        $this->nullable()->live()->partiallyRenderAfterStateUpdated();

        $this->options(static fn (BelongsToSelect $component): ?array => $component->getOptionsFromRelationship());
        $this->getSearchResultsUsing(static fn (BelongsToSelect $component, ?string $search): array => $component->getSearchResultsFromRelationship($search));

        $this->loadStateFromRelationshipsUsing(static function (BelongsToSelect $component): void {
            $component->fillStateFromRelationship();
        });
        $this->saveRelationshipsUsing(static function (BelongsToSelect $component): void {
            $component->saveStateToRelationship();
        });

        $this->configureSearchColumnNames();
        $this->configureGetOptionLabelFromRecordUsing();
        $this->configureGetSelectedRecordUsing();
        $this->configureGetOptionLabelUsing();
        $this->configureManageOptionForm();
        $this->configureCreateOptionUsing();
        $this->configureFillEditOptionActionFormUsing();
        $this->configureUpdateOptionUsing();
        $this->createParentRedirectLink();
    }

    public function relationship(string|Closure|null $name = null, string|Closure|null $titleAttribute = null, ?Closure $modifyQueryUsing = null, bool $ignoreRecord = false): static
    {
        $fieldName = $this->getName();
        $this->relationship = $name ?? (str_ends_with($fieldName, '_id') ? Str::beforeLast($fieldName, '_id') : null);
        $this->relationshipTitleAttribute = $titleAttribute;
        $this->modifyRelationshipQueryUsing = static function (BelongsToSelect $component, Builder $query, ?string $search = null) use ($modifyQueryUsing): Builder {
            $query = $component->modifyQuery($query);

            return $component->evaluate($modifyQueryUsing, [
                'query' => $query, 
                'search' => $search
            ]) ?? $query;
        };
        $this->shouldIgnoreRelationshipRecord = $ignoreRecord;
        $this->cachedRelationshipRecords = null;
        $this->cachedRelationshipOptions = null;
        $this->cachedSelectedRecord = null;

        return $this;
    }

    public function getRelationship(): BelongsTo
    {
        if (! $this->hasRelationship()) {
            throw new LogicException("BelongsToSelect [{$this->getName()}] requires a relationship. Call relationship() with its name or use a field name ending in _id.");
        }

        $relationship = parent::getRelationship();

        if (! $relationship instanceof BelongsTo) {
            throw new LogicException("The relationship [{$this->getRelationshipName()}] on [{$this->getModel()}] must be a BelongsTo relationship.");
        }

        return $relationship;
    }

    public function getRelationshipTitleAttribute(): string
    {
        return parent::getRelationshipTitleAttribute() ?? 'name';
    }

    public function toEmbeddedHtml(): string
    {
        $this->getRelationship();

        return parent::toEmbeddedHtml();
    }

    protected function includeParent(): bool
    {
        return $this->withParent && $this->getRelationship()->getRelated() instanceof ParentRelationship;
    }

    protected function modifyQuery(Builder $query): Builder
    {
        $hasParent = $this->includeParent();
        $model = $query->getModel();

        // Fast path: model uses HasLocalizedQuery - one call handles all join detection.
        if (method_exists($model, 'scopeWithLocalizedData')) {
            $query->withLocalizedData(depth: $hasParent ? 1 : 0);

            if ($hasParent) {
                $query->addParentSelect();
            }

            return $query->addLocalizationSelect();
        }

        if ($hasParent) {
            $query->withLocalizedParent()->with('parent')->addParentSelect();

            if ($this->relationshipParentHasLocalization()) {
                $query->with('parent.localizations');
            }
        }

        if ($this->relationshipHasLocalization()) {
            return $query
                ->withLocalization()
                ->with('localizations')
                ->addLocalizationSelect();
        }

        return $query;
    }

    protected function configureSearchColumnNames(): void
    {
        $this->searchColumnNames(static function (BelongsToSelect $component): array {
            $columns = [
                $component->getRelationship()->getRelated()->qualifyColumn($component->getRelationshipTitleAttribute()),
            ];

            if ($component->relationshipHasLocalization()) {
                $columns[] = '_localizables.value';
                $columns[] = '_localizables.synonyms';
            }

            if ($component->includeParent() && $component->parentSearchable) {
                $columns[] = '_parent.'.$component->parentTitleAttributeName;

                if ($component->relationshipParentHasLocalization()) {
                    $columns[] = '_parent_localizables.value';
                }
            }

            return $columns;
        });
    }

    protected function configureGetOptionLabelFromRecordUsing(): void
    {
        $this->getOptionLabelFromRecordUsing(static function (BelongsToSelect $component, Model $record): string {
            $titleAttribute = $component->getRelationshipTitleAttribute();
            $name = $titleAttribute === 'name' && $record instanceof Localization
                ? $record->display_name
                : (string) data_get($record, str_replace('->', '.', $titleAttribute));

            if (! $component->includeParent()) {
                return $name;
            }

            $parent = $record->parent;
            $parentName = $parent instanceof Localization
                ? $parent->display_name
                : $parent?->name;

            if ($parentName && ($name === $parentName || str_starts_with($name, $parentName.' '))) {
                return $name;
            }

            return $parentName !== null ? "{$name} ({$parentName})" : $name;
        });
    }

    protected function configureGetOptionLabelUsing(): void
    {
        $this->getOptionLabelUsing(static function (BelongsToSelect $component): ?string {
            $record = $component->getSelectedRecord();

            if (! $record) {
                return null;
            }

            return $component->getOptionLabelFromRecord($record);
        });
    }

    protected function configureGetSelectedRecordUsing(): void
    {
        $this->getSelectedRecordUsing(static function (BelongsToSelect $component, int|string $state): ?Model {
            $relationship = Relation::noConstraints(fn () => $component->getRelationship());
            $query = $relationship->getQuery()->where($relationship->getQualifiedOwnerKeyName(), $state);

            if ($component->shouldIgnoreRelationshipRecord && ($record = $component->getRecord()) instanceof Model) {
                $query->where($record->getQualifiedKeyName(), '!=', $record->getKey());
            }

            $query = $component->evaluate($component->modifyRelationshipQueryUsing, [
                'query' => $query, 'search' => null
            ]);

            if (! $query instanceof Builder) {
                return null;
            }

            return $query->first();
        });
    }

    protected function configureCreateOptionUsing(): void
    {
        $this->createOptionUsing(static function (BelongsToSelect $component, array $data, Schema $schema): int|string {
            $relationship = $component->getRelationship();
            $record = $relationship->newModelInstance();
            $record->fill($data);
            $record->save();

            $schema->model($record)->saveRelationships();

            return $record->getAttribute($relationship->getOwnerKeyName());
        });
    }

    protected function configureFillEditOptionActionFormUsing(): void
    {
        $this->fillEditOptionActionFormUsing(static function (BelongsToSelect $component): ?array {
            return $component->getSelectedRecord()?->attributesToArray();
        });
    }

    protected function configureUpdateOptionUsing(): void
    {
        $this->updateOptionUsing(static function (array $data, Schema $schema): void {
            $schema->getRecord()?->update($data);
        });
    }

    protected function createParentRedirectLink(): void
    {
        $this->hint(static function (BelongsToSelect $component): ?Htmlable {
            $record = $component->getSelectedRecord();

            /** @var class-string<Resource>|null $resource */
            $resource = $record ? Filament::getPanel('admin')->getModelResource($record) : null;

            if ($resource === null || ! $resource::hasPage('edit')) {
                return null;
            }

            $label = $component->getModelLabel(Str::headline(class_basename($record)));
            $url = $resource::getUrl('edit', ['record' => $record], panel: 'admin');

            return new HtmlString(
                Blade::render(
                    '<x-filament::link :href="$url" color="default" target="_blank" size="xs">{{ $label }}</x-filament::link>',
                    ['url' => $url, 'label' => __('View :name', ['name' => $label])],
                )
            );
        });
    }
}
