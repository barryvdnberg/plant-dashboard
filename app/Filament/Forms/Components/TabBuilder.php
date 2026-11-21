<?php

namespace App\Filament\Forms\Components;

use App\Filament\Forms\Components\Concerns\HasEmptySchema;
use App\Filament\Forms\Components\TabBuilder\TabSchema;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Filament\Forms\Components\Concerns\HasExtraItemActions;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Contracts\CanEntangleWithSingularRelationships;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class TabBuilder extends Field implements \Filament\Schemas\Components\Contracts\HasExtraItemActions
{
    use CanLimitItemsLength;
    use HasEmptySchema;
    use HasExtraItemActions;

    protected string $view = 'filament.forms.components.tab-builder';

    /** @var array<string, array | Schema | Component | Closure> */
    protected array $tabSchemas = [];

    /** @var array<string, Component> | null */
    protected ?array $cachedTabDefinitions = null;

    /** @var array<string, bool> | null */
    protected ?array $cachedItemsStructure = null;

    protected bool|Closure $isAddable = true;

    protected bool|Closure $isDeletable = true;

    protected bool|Closure $hasInternalLabel = false;

    protected string|Closure|null $enabledItemName = null;

    protected string|Closure|null $addActionLabel = null;

    protected ?Closure $modifyAddActionUsing = null;

    protected ?Closure $modifyDeleteActionUsing = null;

    protected bool|Closure|null $shouldPartiallyRenderAfterActionsCalled = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull()->default([])->childComponents([])->rule('array');
        $this->dehydrated(static function (self $component): bool {
            if (! $component->isSaved()) {
                return false;
            }

            foreach ($component->getTabDefinitions() as $definition) {
                if (! $component->getTabRelationshipName($definition)) {
                    return true;
                }
            }

            return false;
        });
        $this->emptySchema([Text::make(__('No Content'))]);
        $this->afterStateHydrated(static function (self $component): void {
            $component->rawState($component->getActiveState());
        });
        $this->loadStateFromRelationshipsUsing(static function (self $component): void {
            $items = $component->getActiveState();

            foreach ($component->getTabDefinitions() as $key => $definition) {
                if (! $component->getTabRelationshipName($definition)) {
                    continue;
                }

                if ($definition instanceof CanEntangleWithSingularRelationships) {
                    $definition->clearCachedExistingRecord();
                    $hasRelatedState = (bool) $definition->getCachedExistingRecord();
                } else {
                    $hasRelatedState = $definition->getRelationship()?->exists() ?? false;
                }

                if ($hasRelatedState) {
                    $items[$key] ??= [];
                }
            }

            $component->rawState($items);
        });
        // Active tabs use Filament's relationship lifecycle. Removed tabs are
        // deleted only on save, after the entire form has passed validation.
        $this->saveRelationshipsUsing(static function (self $component): void {
            $component->deleteRemovedRelationships();
        });
        $this->mutateDehydratedStateUsing(static fn (self $component, ?array $state): array => $component->dehydrateItems($state));
        $this->registerActions([
            fn (self $component): Action => $component->getAddAction(),
            fn (self $component): Action => $component->getDeleteAction(),
        ]);
    }

    /** @param array<string|int, array | Schema | Component | Closure> $schemas */
    public function schemas(array $schemas): static
    {
        $resolved = [];

        foreach ($schemas as $key => $definition) {
            if (is_int($key)) {
                $key = ($definition instanceof Component) ? $this->getTabRelationshipName($definition) : null;

                if (blank($key)) {
                    throw new InvalidArgumentException('TabBuilder schemas without an explicit key must be components with a relationship name.');
                }
            }

            if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_-]*$/D', $key)) {
                throw new InvalidArgumentException('TabBuilder schema keys must start with a letter or underscore and contain only letters, numbers, underscores or hyphens.');
            }

            if (array_key_exists($key, $resolved)) {
                throw new InvalidArgumentException("Duplicate TabBuilder schema key [{$key}]. Give tabs sharing a relationship distinct explicit keys.");
            }

            $resolved[$key] = $definition;
        }

        $this->tabSchemas = $resolved;
        $this->clearCachedChildSchemas();

        return $this;
    }

    /** @return array<string, Component> */
    public function getTabDefinitions(): array
    {
        if ($this->cachedTabDefinitions !== null) {
            return $this->cachedTabDefinitions;
        }

        $definitions = [];

        foreach ($this->tabSchemas as $key => $schema) {
            $schema = $this->evaluate($schema, ['key' => $key]);
            $definition = ($schema instanceof Component)
                ? $schema->getClone()
                : TabSchema::make($key)->schema($schema instanceof Schema ? $schema->getClone() : $schema);

            // The configured key owns the path, independently of the schema
            // name and the Eloquent relationship name.
            $container = $this->makeChildSchema($key)->statePath($key)->key($key)->inlineLabel(false);
            if (! ($definition instanceof Field)) {
                $definition->statePath(null);
            }

            $definition->key('content')->container($container);
            $container->components([$definition]);
            $definitions[$key] = $definition;
        }

        return $this->cachedTabDefinitions = $definitions;
    }

    protected function getTabRelationshipName(Component $definition): ?string
    {
        return method_exists($definition, 'getRelationshipName') && method_exists($definition, 'getRelationship')
            ? $definition->getRelationshipName()
            : null;
    }

    /** @return array<string, array<string, mixed>> */
    protected function getActiveState(): array
    {
        $state = $this->getRawState();

        return is_array($state)
            ? array_filter(array_intersect_key($state, $this->tabSchemas), is_array(...))
            : [];
    }

    /** @return array<string, Schema> */
    public function getDefaultChildSchemas(): array
    {
        $this->cachedItemsStructure = $this->getItemsStructure();
        $definitions = $this->getTabDefinitions();
        $items = [];

        foreach ($this->getActiveState() as $key => $state) {
            $items[$key] = $definitions[$key]->getContainer();
        }

        return $items;
    }

    /** @return array<string, bool> */
    protected function getItemsStructure(): array
    {
        $state = $this->getRawState();

        return is_array($state) ? array_map(is_array(...), $state) : [];
    }

    protected function areCachedDefaultChildSchemasFresh(): bool
    {
        return $this->cachedItemsStructure === $this->getItemsStructure();
    }

    /** @return array<string, Schema> */
    public function getItems(): array
    {
        return array_filter($this->getCachedDefaultChildSchemas(), fn (Schema $schema): bool => ! $schema->getComponents(withHidden: true)[0]->isHidden());
    }

    /** @return array<string, mixed> */
    public function getItemState(string $key): array
    {
        $definition = $this->getTabDefinitions()[$key];

        if ($definition instanceof Field && $this->getTabRelationshipName($definition)) {
            $this->getChildSchema($key)->validate();

            return [$definition->getName() => $definition->getState()];
        }

        if (($definition instanceof CanEntangleWithSingularRelationships) && $definition->getRelationshipName()) {
            return $definition->getChildSchema()->getState(shouldCallHooksBefore: false);
        }

        return $this->getChildSchema($key)->getState(shouldCallHooksBefore: false);
    }

    /** @return array<string, array<string, mixed>> */
    public function dehydrateItems(?array $state): array
    {
        $items = [];

        foreach ($state ?? [] as $key => $data) {
            $definition = $this->getTabDefinitions()[$key] ?? null;

            if (! $definition || ! is_array($data)) {
                continue;
            }

            if ($this->getTabRelationshipName($definition)) {
                continue;
            }

            $items[$key] = $data;
        }

        return $items;
    }

    protected function deleteRemovedRelationships(): void
    {
        $items = $this->getActiveState();

        foreach ($this->getTabDefinitions() as $key => $definition) {
            if (array_key_exists($key, $items) || ! $this->getTabRelationshipName($definition)) {
                continue;
            }

            if ($definition->isHidden() || $definition->isDisabled()) {
                continue;
            }

            if ($this->hasActiveTabForRelationship($definition, $items)) {
                continue;
            }

            if ($definition instanceof Field) {
                // Let the field define how to clear its relationship. A Select
                // detaches pivot rows rather than deleting the selected records.
                try {
                    $definition->state(($definition instanceof Select && ! $definition->isMultiple()) ? null : []);
                    $definition->saveRelationships();
                } finally {
                    $this->rawState($items);
                }

                continue;
            }

            if (! ($definition instanceof CanEntangleWithSingularRelationships)) {
                continue;
            }

            $record = $definition->getCachedExistingRecord();

            if (! $record) {
                continue;
            }

            $relationship = $definition->getRelationship();

            if ($relationship instanceof BelongsTo) {
                $relationship->dissociate();
                $relationship->getParent()->save();
            }

            $record->delete();
            $definition->clearCachedExistingRecord();
            $this->getRecord()->unsetRelation($definition->getRelationshipName());
        }
    }

    /** @param array<string, array<string, mixed>> $items */
    protected function hasActiveTabForRelationship(Component $definition, array $items): bool
    {
        foreach (array_intersect_key($this->getTabDefinitions(), $items) as $activeDefinition) {
            if ($this->getTabRelationshipName($activeDefinition) !== $this->getTabRelationshipName($definition)) {
                continue;
            }

            if (($definition instanceof Field) && ($activeDefinition instanceof Field)) {
                $query = $definition->getRelationship()?->getQuery();
                $activeQuery = $activeDefinition->getRelationship()?->getQuery();

                // Fields can edit separate pivot scopes on the same relation.
                if ($query && $activeQuery && [
                    $query->toSql(), $query->getBindings(),
                ] !== [
                    $activeQuery->toSql(), $activeQuery->getBindings(),
                ]) {
                    continue;
                }
            }

            return true;
        }

        return false;
    }

    public function getItemLabel(string $key): string|Htmlable
    {
        $definition = $this->getTabDefinitions()[$key];

        return ($definition instanceof TabSchema || $definition instanceof Field)
            ? ($definition->getLabel() ?? str($key)->headline()->toString())
            : str($key)->headline()->toString();
    }

    /** @return array<string, string> */
    public function getAvailableOptions(): array
    {
        $items = $this->getActiveState();
        $options = [];

        foreach ($this->getTabDefinitions() as $key => $definition) {
            if (! array_key_exists($key, $items) && ! $definition->isHidden() && ! $definition->isDisabled()) {
                $options[$key] = strip_tags((string) $this->getItemLabel($key));
            }
        }

        return $options;
    }

    public function getAddAction(): Action
    {
        $action = Action::make($this->getAddActionName())
            ->label(fn (self $component): string => $component->evaluate($component->addActionLabel) ?? __('filament-forms::components.repeater.actions.add.label', ['label' => $component->getLabel()]))
            ->icon(Heroicon::OutlinedPlusCircle)->color('gray')->button()->size(Size::Large)
            ->visible(fn (self $component): bool => $component->isAddable())
            ->schema(fn (self $component): array => [
                Select::make('key')->label(__('Type'))->options($component->getAvailableOptions())->required(),
            ])
            ->action(static function (self $component, array $data): void {
                $key = $data['key'] ?? null;

                if (! $component->isAddable() || ! array_key_exists($key ?? '', $component->getAvailableOptions())) {
                    return;
                }

                $items = $component->getActiveState();
                $items[$key] = [];
                $component->rawState($items);
                $component->getChildSchema($key)->fill();
                $component->callAfterStateUpdated();
                $component->renderAfterAction();
            });

        return $this->modifyAddActionUsing ? ($this->evaluate($this->modifyAddActionUsing, ['action' => $action]) ?? $action) : $action;
    }

    public function getDeleteAction(): Action
    {
        $action = Action::make($this->getDeleteActionName())
            ->label(__('filament-forms::components.repeater.actions.delete.label'))
            ->icon(Heroicon::Trash)->color('danger')->iconButton()->size(Size::Small)
            ->visible(fn (self $component): bool => $component->isDeletable())
            ->action(static function (self $component, array $arguments): void {
                $key = $arguments['item'] ?? null;
                $items = $component->getActiveState();

                if (! $component->isDeletable() || ! array_key_exists($key ?? '', $items)) {
                    return;
                }

                unset($items[$key]);
                $component->rawState($items);
                $component->callAfterStateUpdated();
                $component->renderAfterAction();
            });

        return $this->modifyDeleteActionUsing ? ($this->evaluate($this->modifyDeleteActionUsing, ['action' => $action]) ?? $action) : $action;
    }

    public function getAddActionName(): string
    {
        return 'add';
    }

    public function getDeleteActionName(): string
    {
        return 'delete';
    }

    public function addAction(?Closure $callback): static
    {
        $this->modifyAddActionUsing = $callback;

        return $this;
    }

    public function deleteAction(?Closure $callback): static
    {
        $this->modifyDeleteActionUsing = $callback;

        return $this;
    }

    public function addActionLabel(string|Closure|null $label): static
    {
        $this->addActionLabel = $label;

        return $this;
    }

    public function addable(bool|Closure $condition = true): static
    {
        $this->isAddable = $condition;

        return $this;
    }

    public function deletable(bool|Closure $condition = true): static
    {
        $this->isDeletable = $condition;

        return $this;
    }

    public function isAddable(): bool
    {
        return ! $this->isDisabled() && $this->evaluate($this->isAddable)
            && (($this->getMaxItems() === null) || ($this->getItemsCount() < $this->getMaxItems()))
            && filled($this->getAvailableOptions());
    }

    public function isDeletable(): bool
    {
        return ! $this->isDisabled() && $this->evaluate($this->isDeletable)
            && (($this->getMinItems() === null) || ($this->getItemsCount() > $this->getMinItems()));
    }

    public function internalLabel(bool|Closure $condition = true): static
    {
        $this->hasInternalLabel = $condition;

        return $this;
    }

    public function hasInternalLabel(): bool
    {
        return (bool) $this->evaluate($this->hasInternalLabel);
    }

    public function enabledItemName(string|Closure|null $key): static
    {
        $this->enabledItemName = $key;

        return $this;
    }

    public function getEnabledItemName(): ?string
    {
        return $this->evaluate($this->enabledItemName);
    }

    public function partiallyRenderAfterActionsCalled(bool|Closure|null $condition = true): static
    {
        $this->shouldPartiallyRenderAfterActionsCalled = $condition;

        return $this;
    }

    public function shouldPartiallyRenderAfterActionsCalled(): bool
    {
        return $this->evaluate($this->shouldPartiallyRenderAfterActionsCalled) ?? ! $this->isLive();
    }

    protected function renderAfterAction(): void
    {
        if ($this->shouldPartiallyRenderAfterActionsCalled()) {
            $this->partiallyRender();
        }
    }

    public function clearCachedChildSchemas(): void
    {
        parent::clearCachedChildSchemas();
        $this->cachedTabDefinitions = null;
        $this->cachedItemsStructure = null;
    }

    protected function cloneChildComponents(): static
    {
        parent::cloneChildComponents();
        $this->cachedTabDefinitions = null;
        $this->cachedItemsStructure = null;

        return $this;
    }
}
