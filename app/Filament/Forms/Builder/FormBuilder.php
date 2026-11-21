<?php

namespace App\Filament\Forms\Builder;

use App\Enums\FormOrder;
use App\Filament\Forms\Builder\Contracts\NameField;
use App\Filament\Forms\Builder\Contracts\ParentField;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

abstract class FormBuilder
{
    public static FormOrder $order = FormOrder::NAME_FIRST;
    public static function form(): array
    {
        $components = [];

        if (static::$order === FormOrder::NAME_FIRST) {
            static::addNameField($components);
            static::addParentField($components);
        }
        else {
            static::addParentField($components);
            static::addNameField($components);
        }

        return [
            ...$components,
            ...static::attributeFields(),
        ];
    }

    public static function addNameField(array &$components)
    {
        static::addField($components, NameField::class, 'nameField');
    }

    public static function addParentField(array &$components)
    {
        static::addField($components, ParentField::class, 'parentField');
    }

    private static function addField(array &$components, string $fieldClass, string $method)
    {
        if (is_subclass_of(static::class, $fieldClass)) {
            $components[] = forward_static_call([static::class, $method]);
        }
    }

    /**
     * @return array<Component | Action | ActionGroup | string | Htmlable>
     */
    public abstract static function attributeFields(): array;
}