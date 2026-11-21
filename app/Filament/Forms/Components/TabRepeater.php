<?php

namespace App\Filament\Forms\Components;

use App\Filament\Forms\Components\Concerns\CanBeEdited;
use App\Filament\Forms\Components\Concerns\HasDynamicItemActions;
use App\Filament\Forms\Components\Concerns\HasEmptySchema;
use App\Filament\Forms\Components\Concerns\HasKeyField;
use App\Filament\Forms\Components\Concerns\HasLabels;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Concerns\CanDisableOptions;
use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Arrayable;

class TabRepeater extends Repeater
{
    use HasLabels;
    use HasOptions;
    use HasKeyField;
    use HasDynamicItemActions;
    use CanBeEdited {
        IsEditable as protected traitIsEditable;
    }
    use HasEmptySchema;
    use CanDisableOptions;

    protected string | Closure | null $enabledItemName = null;

    protected bool | Closure | null $hasInternalLabel = null;

    protected ?Closure $modifyEditActionUsing = null;

    protected ?Closure $getOptionLabelUsing = null;

    protected ?Schema $emptySchema = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull()->defaultItems(0);

        $this->registerActions([
            fn (TabRepeater $component) => $component->getEditAction(),
        ]);

        $this->cloneAction(static function (Action $action) {
            $action->schema(static fn (TabRepeater $component): array => [
                $component->getKeyField(),
            ]);

            $actionFunction = $action->getActionFunction();

            $action->action(static function (TabRepeater $component, $arguments, $data) use ($actionFunction): void {
                $actionFunction($arguments, $component);

                if (filled($data)) {
                    $items = $component->getRawState();

                    $schema = $component->getChildSchema(array_key_last($items));

                    $schema->getComponent($component->getKeyFieldName())->state($data[$component->getKeyFieldName()] ?? null);
                }
            });
        });

        $this->addAction(static function (Action $action) {
            $action->schema(static fn (TabRepeater $component): array => [
                $component->getKeyField(),
            ]);

            $actionFunction = $action->getActionFunction();

            $action->action(static function (TabRepeater $component, $data) use ($actionFunction): void {
                $actionFunction($component);

                if (filled($data)) {
                    $items = $component->getRawState();

                    $schema = $component->getChildSchema(array_key_last($items));

                    $schema->getComponent($component->getKeyFieldName())->state($data[$component->getKeyFieldName()] ?? null);
                }
            });

            $action
                ->icon(Heroicon::OutlinedPlusCircle)
                ->size(Size::Large)
                ->button()
                ->label('add');
        });

        $this->emptySchema(static function () {
            return Schema::make()->schema(Text::make(__('No Content')));
        });

        $this->itemLabel(static function (TabRepeater $component, Schema $container) {
            $labels = $component->getOptions();
            $value = $container->getRawState()[$component->getKeyFieldName()] ?? null;

            return $labels[$value] ?? null;
        });

        $this->disableOptionWhen(static function (TabRepeater $component, $value, array|null $state) {
            $items = array_values($state ?? []);

            $usedOptions = array_map(fn($item) => $item[$component->getKeyFieldName()] ?? null, $items);

            return in_array($value, $usedOptions);
        });
    }

    /**
     * @param  array<string | array<string>> | Arrayable | string | Closure | null  $options
     */
    public function options(array | Arrayable | string | Closure | null $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function disableOptionWhen(bool | Closure | null $callback): static
    {
        $this->isOptionDisabled[] = $callback;

        return $this;
    }

    public function internalLabel(bool | Closure | null $label = true): static
    {
        $this->hasInternalLabel = $label;

        return $this;
    }

    public function modifyEditActionUsing(?Closure $callback): static
    {
        $this->modifyEditActionUsing = $callback;

        return $this;
    }

    public function getOptionLabelUsing(?Closure $callback): static
    {
        $this->getOptionLabelUsing = $callback;

        return $this;
    }

    public function enabledItemName(string | Closure | null $name): static
    {
        $this->enabledItemName = $name;

        return $this;
    }

    public function hasInternalLabel(): bool
    {
        return $this->evaluate($this->hasInternalLabel) ?? false;
    }

    public function getEditAction(): Action
    {
        return Action::make($this->getEditActionName())
            ->icon('heroicon-s-pencil-square')
            ->iconButton()
            ->color('gray')
            ->size(Size::Small)
            ->fillForm(static function (TabRepeater $component, $arguments): array {
                if (!array_key_exists('item', $arguments)) {
                    return [];
                }

                $schema = $component->getChildSchema($arguments['item']);

                $state = $schema->getComponent($component->getKeyFieldName())->getState();
                return [
                    $component->getKeyFieldName() => $state,
                ];
            })
            ->schema(static fn (TabRepeater $component): array => [
                $component->getKeyField()
            ])
            ->action(static function ($component, $data, $arguments): void {
                if (!array_key_exists('item', $arguments)) {
                    return;
                }

                $schema = $component->getChildSchema($arguments['item']);

                $schema->getComponent($component->getKeyFieldName())->state($data[$component->getKeyFieldName()] ?? null);
            });
    }

    public function getEditActionName(): string
    {
        return 'edit';
    }

    public function getEnabledItemName(): ?string
    {
        return $this->evaluate($this->enabledItemName) ?? null;
    }

    public function schema(array|Schema|Closure $components): static
    {
        return parent::schema(static function (TabRepeater $component) use ($components): Schema|array {
            if ($components instanceof Closure) {
                $components = $component->evaluate($components);
            }

            if ($components instanceof Schema) {
                return $components->components($component->getMergedComponents(
                    $components->getComponents(),
                    $component
                ));
            }

            return $component->getMergedComponents(
                $components,
                $component
            );
        });
    }

    protected function getMergedComponents(array $components, TabRepeater $component): array
    {
        return array_merge(
            [$component->getHiddenKeyField()],
            $components
        );
    }

    public function getDefaultView(): string
    {
        return 'filament.forms.components.tab-repeater';
    }

    public function isEditable(): bool
    {
        if (! $this->hasEnabledOptions()) {
            return false;
        }
        return $this->traitIsEditable();
    }

    public function isAddable(): bool
    {
        if (! $this->hasEnabledOptions()) {
            return false;
        }
        return parent::isAddable();
    }

    public function isCloneable(): bool
    {
        if (! $this->hasEnabledOptions()) {
            return false;
        }

        return parent::isCloneable();
    }

    public function hasEnabledOptions(): bool
    {
        if ($this->options) {
            $availableOptions = $this->getEnabledOptions();
            if (blank($availableOptions)) {
                return false;
            }
        }

        return true;
    }
}
