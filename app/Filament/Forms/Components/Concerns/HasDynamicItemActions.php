<?php

namespace App\Filament\Forms\Components\Concerns;

use Closure;

/**
 * Trait that provides dynamic item actions for form components, allowing items to be editable, deletable, and cloneable based on conditions.
 */
trait HasDynamicItemActions
{
    protected bool | Closure $itemEditable = true;

    protected bool | Closure $itemDeletable = true;

    protected bool | Closure $itemCloneable = true;

    /**
     * Set the condition for whether an item can be editable.
     *
     * @param bool | Closure $condition
     * @return static
     */
    public function itemEditable(bool | Closure $condition = true): static
    {
        $this->itemEditable = $condition;

        return $this;
    }

    /**
     * Set the condition for whether an item can be deletable.
     *
     * @param bool | Closure $condition
     * @return static
     */
    public function itemDeletable(bool | Closure $condition = true): static
    {
        $this->itemDeletable = $condition;

        return $this;
    }

    /**
     * Set the condition for whether an item can be cloneable.
     *
     * @param bool | Closure $condition
     * @return static
     */
    public function itemCloneable(bool | Closure $condition = true): static
    {
        $this->itemCloneable = $condition;

        return $this;
    }

    public function isItemEditable(string $key): bool
    {
        if (! $this->itemEditable) {
            return true;
        }

        $container = $this->getChildSchema($key);

        return (bool) $this->evaluate($this->itemEditable, [
            'container' => $container,
            'item' => $container->getRawState(),
            'key' => $key,
            'uuid' => $key
        ]);
    }

    public function isItemDeletable(string $key): bool
    {
        if (! $this->itemDeletable) {
            return true;
        }

        $container = $this->getChildSchema($key);

        return (bool) $this->evaluate($this->itemDeletable, [
            'container' => $container,
            'item' => $container->getRawState(),
            'key' => $key,
            'uuid' => $key
        ]);
    }

    public function isItemCloneable(string $key): bool
    {
        if (! $this->itemCloneable) {
            return true;
        }

        $container = $this->getChildSchema($key);

        return (bool) $this->evaluate($this->itemCloneable, [
            'container' => $container,
            'item' => $container->getRawState(),
            'key' => $key,
            'uuid' => $key
        ]);
    }
}