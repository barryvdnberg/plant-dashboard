<?php

namespace App\Filament\Forms\Components;

use App\Filament\Forms\Components\Concerns\HasLabels;
use Filament\Forms\Components\Field;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Concerns\CanBeSecondary;
use Filament\Schemas\Schema;

/**
 * @deprecated use `TabRepeater` instead.
 */
class TestRepeater extends Repeater
{
    use CanBeSecondary;
    use HasLabels;
    use HasOptions;

    protected string | Closure | null $enabledItemName = null;

    protected Field | Closure | null $keyField = null;

    protected ?Closure $itemDeletable = null;
    
    protected ?Closure $modifyKeyFieldUsing = null;

    protected ?Closure $getOptionLabelUsing = null;

    protected string $keyFieldName = 'group';

    public function setUp(): void
    {
        parent::setUp();

        $this->hiddenLabel()->columns(10)->secondary()->columnSpanFull();

        $this->default(static function (TestRepeater $component): array {
            return [[$component->getKeyFieldName() => 'default']];
        });

        $this->extraItemActions([
            Action::make('edit')
                ->icon('heroicon-s-pencil-square')
                ->fillForm(static function (TestRepeater $component, $arguments): array {
                    if (!array_key_exists('item', $arguments)) {
                        return [];
                    }

                    $schema = $component->getChildSchema($arguments['item']);

                    $state = $schema->getComponent($component->getKeyFieldName())->getState();
                    return [
                        $component->getKeyFieldName() => $state,
                    ];
                })
                ->schema(static fn (TestRepeater $component): array => [
                    $component->getKeyField()
                ])
                ->action(static function ($component, $data, $arguments): void {
                    if (!array_key_exists('item', $arguments)) {
                        return;
                    }

                    $schema = $component->getChildSchema($arguments['item']);

                    $schema->getComponent($component->getKeyFieldName())->state($data[$component->getKeyFieldName()] ?? null);
                })
        ]);

        $this->cloneAction(static function (Action $action) {
            $action->schema(static fn (TestRepeater $component): array => [
                $component->getKeyField(),
            ]);

            $actionFunction = $action->getActionFunction();

            $action->action(static function (TestRepeater $component, $arguments, $data) use ($actionFunction): void {
                $actionFunction($arguments, $component);

                if (filled($data)) {
                    $items = $component->getRawState();

                    $schema = $component->getChildSchema(array_key_last($items));

                    $schema->getComponent($component->getKeyFieldName())->state($data[$component->getKeyFieldName()] ?? null);
                }
            });
        });

        $this->addAction(static function (Action $action) {
            $action->schema(static fn (TestRepeater $component): array => [
                $component->getKeyField(),
            ]);

            $actionFunction = $action->getActionFunction();

            $action->action(static function (TestRepeater $component, $data) use ($actionFunction): void {
                $actionFunction($component);

                if (filled($data)) {
                    $items = $component->getRawState();

                    $schema = $component->getChildSchema(array_key_last($items));

                    $schema->getComponent($component->getKeyFieldName())->state($data[$component->getKeyFieldName()] ?? null);
                }
            });
        });
    }

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

    public function getOptionLabelUsing(?Closure $callback): static
    {
        $this->getOptionLabelUsing = $callback;

        return $this;
    }

    public function modifyKeyFieldUsing(Closure $callback): static
    {
        $this->modifyKeyFieldUsing = $callback;

        return $this;
    }

    public function enabledItemName(string | Closure | null $name): static
    {
        $this->enabledItemName = $name;

        return $this;
    }

    public function getEnabledItemName(): string
    {
        return $this->evaluate($this->enabledItemName) ?? 'default';
    }

    public function itemDeletable(?Closure $condition): static
    {
        $this->itemDeletable = $condition;

        return $this;
    }

    public function isItemDeletable(string $key): bool
    {
        if (! $this->itemDeletable) {
            return true;
        }

        $container = $this->getChildSchema($key);

        return (bool) $this->evaluate($this->itemDeletable, [
            'container' => $container,
            'item' => $container,
            'key' => $key,
            'schema' => $container,
            'state' => $container->getRawState(),
            'uuid' => $key,
        ]);
    }


    public function getChildSchema($key = null): ?Schema
    {
        if (filled($key) && array_key_exists($key, $this->cachedDefaultChildSchemas ??= $this->getDefaultChildSchemas())) {
            return $this->cachedDefaultChildSchemas[$key];
        }

        $key ??= 'default';

        $components = ($key === 'default')
            ? $this->getDefaultChildComponents()
            : $this->evaluate($this->childComponents[$key] ?? []) ?? [];

        if (blank($components)) {
            return ($key === 'default')
                ? $this->configureChildSchema(
                    $this->makeChildSchema($key)
                        ->components([$this->getHiddenKeyField()]),
                    $key,
                )
                : null;
        }

        if ($components instanceof Schema) {
            $components->components(array_merge(
                [$this->getHiddenKeyField()], 
                $components->getComponents()
            ));

            return $this->configureChildSchema(
                $components
                    ->livewire($this->getLivewire())
                    ->parentComponent($this),
                $key,
            );
        }

        $components = array_merge(
            [$this->getHiddenKeyField()], 
            $components
        );
        
        return $this->configureChildSchema(
            $this->makeChildSchema($key)
                ->components($components),
            $key,
        );
    }

    public function getDefaultView(): string
    {
        return 'filament.forms.components.test-repeater';
    }

    protected function getKeyField(): Field
    {
        $component = $this->evaluate($this->keyField) ?? $this->keyField;

        if (empty($component) && filled($this->options)) {
            $component = Select::make($this->keyFieldName)
                ->options(fn () => $this->getOptions());

            if ($this->getOptionLabelUsing) {
                $component->getOptionLabelUsing($this->getOptionLabelUsing);
            }
        }

        if (empty($component)) {
            $component = TextInput::make($this->keyFieldName);
        }
        
        if ($this->modifyKeyFieldUsing) {
            $this->evaluate($this->modifyKeyFieldUsing, ['component' => $component]);
        }

        return $component;
    }

    protected function getHiddenKeyField(): Field
    {
        return Hidden::make($this->getKeyFieldName());
    }

    protected function getKeyFieldName(): string
    {
        return $this->getKeyField()->getName();
    }

    public function isAddable(): bool
    {
        if ($this->isDisabled()) {
            return false;
        }

        if (filled($this->getMaxItems()) && ($this->getMaxItems() <= $this->getItemsCount())) {
            return false;
        }

        return (bool) $this->evaluate($this->isAddable, [
            'component' => $this,
        ]);
    }


    
}