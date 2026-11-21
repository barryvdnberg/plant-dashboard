<?php

namespace App\Filament\Forms\Components;

use App\Models\Contracts\Localization;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class BelongsToSelect extends Select
{
    use Concerns\HasSearchColumnNames;
    use Concerns\RelationshipCanHaveFormSchema;
    use Concerns\RelationshipCanHaveLocalization;
    use Concerns\RelationshipCanHaveModelLabel;

    protected bool $withParent = false;

    protected bool $parentSearchable = false;

    // /** @var class-string<\Illuminate\Database\Eloquent\Model>|null $parentModel */
    // protected ?string $parentModelClass = null;
    protected string $parentAliasName = '_parent';

    protected string $parentTitleAttributeName = 'name';

    protected string $parentTitleSelectAlias = '_parent_name';

    protected ?string $relationshipName = null;

    protected string $relationshipTitleAttributeName = 'name';

    /**
     * Loads the query with it's parent
     */
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

    public function usingRelationship(string $relationshipName): static
    {
        $this->relationshipName = $relationshipName;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $name = $this->getName();
        if (str_ends_with($name, '_id')) {
            $this->relationshipName = Str::beforeLast($name, '_id');
        }

        $this->configureLabel();

        $this->nullable();

        $this->live();
        $this->partiallyRenderAfterStateUpdated();

        // base setup for relationship
        $this->configureRelationship();

        // required data for searching
        $this->configureSearchColumnNames();

        // retrieves search results and their labels
        $this->configureGetSearchResultsUsing();
        $this->configureGetOptionLabelFromRecordUsing();

        // retrieves the current selected record and its label
        $this->configureGetSelectedRecordUsing();
        $this->configureGetOptionLabelUsing();

        // Manage option form (create/edit)
        $this->configureManageOptionForm();

        // Create option form action save logic
        $this->configureCreateOptionUsing();

        // Update option form action load and save logic
        $this->configureFillEditOptionActionFormUsing();
        $this->configureUpdateOptionUsing();

        // Redirect link to parent record edit page
        $this->createParentRedirectLink();

    }

    protected function includeParent(): bool
    {
        return $this->withParent;
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

        // Fallback for simpler models (e.g. Color) that implement Localization directly
        // but are not query-root models and therefore don't use HasLocalizedQuery.
        if ($this->relationshipHasLocalization()) {
            return $query
                ->withLocalization()
                ->with('localizations')
                ->addLocalizationSelect();
        }

        if ($hasParent) {
            return $query
                ->withParent()
                ->addParentSelect();
        }

        return $query;
    }

    protected function configureRelationship(): void
    {
        // manually setting up relationship instead of relying on the relationship() function
        $this->relationship = fn () => $this->relationshipName;
        $this->relationshipTitleAttribute = fn () => $this->relationshipTitleAttributeName;
    }

    protected function configureSearchColumnNames(): void
    {
        $this->searchColumnNames(static function (BelongsToSelect $component) {
            $columns = [
                $component->getRelationship()->getRelated()->getTable().'.'.$component->getRelationshipTitleAttribute(),
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

    protected function configureGetSearchResultsUsing(): void
    {
        $this->getSearchResultsUsing(static function (BelongsToSelect $component, ?string $search) {
            $relationship = $component->getRelationshipNoConstraints();

            $relationshipQuery = $relationship->getQuery();

            $component->modifyQuery($relationshipQuery);

            $relationshipQuery->where(static function (Builder $query) use ($component, $search) {
                $isFirst = true;

                foreach ($component->getSearchColumns() ?? [] as $searchColumn) {
                    $whereClause = $isFirst ? 'where' : 'orWhere';

                    $query->{$whereClause}(
                        $searchColumn,
                        'like',
                        "%{$search}%",
                    );

                    $isFirst = false;
                }

                return $query;
            });

            $relationshipQuery->limit($component->getOptionsLimit());

            $qualifiedRelatedKeyName = $relationship->getQualifiedOwnerKeyName();

            return $relationshipQuery
                ->get()
                ->mapWithKeys(static fn (Model $record) => [
                    $record->{Str::afterLast($qualifiedRelatedKeyName, '.')} => $component->getOptionLabelFromRecord($record),
                ])
                ->toArray();
        });
    }

    protected function configureGetOptionLabelFromRecordUsing(): void
    {
        $this->getOptionLabelFromRecordUsing(static function (BelongsToSelect $component, Model $record) {
            $hasLocalization = $component->relationshipHasLocalization();
            $hasParent = $component->includeParent();

            $name = $hasLocalization
                ? $record->display_name
                : ($record->name ?? '');

            if (! $hasParent) {
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
        $this->getOptionLabelUsing(static function (BelongsToSelect $component) {
            $record = $component->getSelectedRecord();

            if (! $record) {
                return null;
            }

            return $component->getOptionLabelFromRecord($record);
        });
    }

    protected function configureGetSelectedRecordUsing(): void
    {
        $this->getSelectedRecordUsing(static function (BelongsToSelect $component, int $state): ?Model {
            // should always be an integer as it is a foreign key
            $relationship = $component->getRelationshipNoConstraints();

            $relationshipQuery = $relationship->getQuery();

            $relationshipQuery->where($relationship->getQualifiedOwnerKeyName(), $state);

            $component->modifyQuery($relationshipQuery);

            return $relationshipQuery->first();
        });
    }

    protected function configureCreateOptionUsing(): void
    {
        $this->createOptionUsing(static function (BelongsToSelect $component, array $data, Schema $schema) {
            $record = $component->getRelationship()->getRelated();
            $record->fill($data);
            $record->save();

            $schema->model($record)->saveRelationships();

            return $record->getKey();
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

    /**
     * Return the underlying BelongsTo relationship
     */
    protected function getRelationshipNoConstraints(): BelongsTo
    {
        /** @var BelongsTo $relationship */
        return Relation::noConstraints(fn () => $this->getRelationship());
    }

    protected function createParentRedirectLink(): void
    {
        $this->hint(function (Get $get) {
            $relationshipName = $this->getRelationshipName();
            $id = $get($this->getName());
            if (! $id) {
                return null;
            }

            $label = $this->getModelLabel(Str::ucfirst($this->relationshipName));

            $string = __('View '.':name', [
                'name' => $label,
            ]);

            $url = route('filament.admin.resources.'.Str::plural($relationshipName).'.edit', [
                'record' => $id,
            ]);

            return new HtmlString(
                Blade::render(
                    '<x-filament::link :href="$url" color="default" target="_blank" size="xs">{{ $label }}</x-filament::link>',
                    ['url' => $url, 'label' => $string],
                )
            );
        });
    }
}
