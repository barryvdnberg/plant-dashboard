@php
    use Filament\Actions\Action;
    use Filament\Support\Enums\Alignment;
    use Illuminate\View\ComponentAttributeBag;

    $fieldWrapperView = $getFieldWrapperView();

    $items = $getItems();
    $enabledItemName = $getEnabledItemName();
    $secondary = $isSecondary();
    $repeaterStatePath = $getStatePath();

    // Derive the toggle's group => label lookup from the same itemLabel()
    // closure used for the item headers, so it only needs defining once.
    $labels = $getLabels();
    $addAction = $getAction($getAddActionName());
    $addActionAlignment = $getAddActionAlignment();
    $cloneAction = $getAction($getCloneActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $extraItemActions = $getExtraItemActions();

    $hasItemNumbers = $hasItemNumbers();
    $hasItemHeaders = $hasItemHeaders();
    $isAddable = $isAddable();
    $isCloneable = $isCloneable();
    $isDeletable = $isDeletable();

    $key = $getKey();

    $itemLabelHeadingTag = $getHeadingTag();
    $isItemLabelTruncated = $isItemLabelTruncated();

    // Select whichever existing item currently has the enabled group, falling
    // back to the first item, so the selection is tracked by stable item key
    // rather than the item's (editable) group value.
    $enabledItemKey = null;

    foreach ($items as $itemKey => $item) {
        if (($item->getRawState()['group'] ?? null) === $enabledItemName) {
            $enabledItemKey = $itemKey;

            break;
        }
    }

    $enabledItemKey ??= array_key_first($items);
@endphp
<div
    x-data="{
        selectedItemKey: @js($enabledItemKey),
        labels: @js($labels),
        items: $wire.entangle(@js($repeaterStatePath)),
        get options() {
            const seenGroups = new Set();
            const options = [];

            for (const [itemKey, item] of Object.entries(this.items ?? {})) {
                const group = item?.group;

                if (! group || seenGroups.has(group)) {
                    continue;
                }

                seenGroups.add(group);
                options.push({ key: itemKey, group });
            }

            return options;
        },
    }"
    x-init="$watch('items', (items) => { if (! Object.keys(items ?? {}).includes(selectedItemKey)) { selectedItemKey = Object.keys(items ?? {})[0] ?? null } })"
    {{
        $attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)
    }}
>
    <div class="fi-sc fi-sc-has-gap fi-grid lg:fi-grid-cols" style="--cols-lg: repeat(10, minmax(0, 1fr)); --cols-default: repeat(1, minmax(0, 1fr));">
        <div class="fi-grid-col lg:fi-grid-col-span" style="--col-span-default: span 1 / span 1; --col-span-lg: span 3 / 3;">
            <x-filament::section :secondary="$secondary">
                <div class="fi-sc fi-sc-has-gap fi-sc-dense fi-grid lg:fi-grid-cols" style="--cols-lg: repeat(1, minmax(0, 1fr)); --cols-default: repeat(1, minmax(0, 1fr));">
                    <template x-for="option in options" :key="option.key">
                        <x-filament::button color="gray"
                            x-on:click="selectedItemKey = option.key"
                            tag="div"
                            x-bind:class="selectedItemKey === option.key ? 'fi-color fi-color-primary fi-bg-color-400 hover:fi-bg-color-300 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-900 hover:fi-text-color-800 dark:fi-text-color-950 dark:hover:fi-text-color-950' : ''"
                            x-text="labels[option.group] ?? option.group"
                        ></x-filament::button>
                    </template>
                    @if ($addAction && $addAction->isVisible())
                        <div>
                            {{ $addAction }}
                        </div>
                    @endif
                </div>
            </x-filament::section>
        </div>
        <div class="fi-grid-col lg:fi-grid-col-span" style="--col-span-default: span 1 / span 1; --col-span-lg: span 9 / 12;">
            <x-dynamic-component :component="$fieldWrapperView" :field="$field">
                <div
                    {{
                        $attributes
                            ->merge($getExtraAttributes(), escape: false)
                            ->class([
                                'fi-fo-repeater',
                            ])
                    }}
                >
                    @if (count($items))
                        <ul
                            x-sortable
                            {{
                                (new ComponentAttributeBag)
                                    ->grid($getGridColumns())
                                    ->merge([
                                        // 'data-sortable-animation-duration' => $getReorderAnimationDuration(),
                                        'x-on:end.stop' => '$wire.mountAction(\'reorder\', { items: $event.target.sortable.toArray() }, { schemaComponent: \'' . $key . '\' })',
                                    ], escape: false)
                                    ->class(['fi-fo-repeater-items'])
                            }}
                        >
                            @foreach ($items as $itemKey => $item)
                                @php
                                    $itemLabel = $getItemLabel($itemKey, $loop->index);
                                    $visibleExtraItemActions = array_filter(
                                        $extraItemActions,
                                        fn (Action $action): bool => $action(['item' => $itemKey])->isVisible(),
                                    );
                                    $cloneAction = $cloneAction(['item' => $itemKey]);
                                    $cloneActionIsVisible = $isCloneable && $cloneAction->isVisible();
                                    $deleteAction = $deleteAction(['item' => $itemKey]);
                                    $deleteActionIsVisible = $isDeletable && $isItemDeletable($itemKey);
                                    $hasItemHeader = $hasItemHeaders && (filled($itemLabel) || $cloneActionIsVisible || $deleteActionIsVisible || $visibleExtraItemActions);
                                @endphp

                                <li
                                    wire:ignore.self
                                    wire:key="{{ $item->getLivewireKey() }}.item"
                                    x-sortable-item="{{ $itemKey }}"
                                    x-show="selectedItemKey === @js($itemKey)"
                                    @class([
                                        'fi-fo-repeater-item',
                                        'fi-fo-repeater-item-has-header' => $hasItemHeader,
                                    ])
                                >
                                    @if ($hasItemHeader)
                                        <div
                                            class="fi-fo-repeater-item-header"
                                        >
                                            @if (filled($itemLabel))
                                                <{{ $itemLabelHeadingTag }}
                                                    @class([
                                                        'fi-fo-repeater-item-header-label',
                                                        'fi-truncated' => $isItemLabelTruncated,
                                                    ])
                                                >
                                                    {{ $itemLabel }}

                                                    @if ($hasItemNumbers)
                                                        {{ $loop->iteration }}
                                                    @endif
                                                </{{ $itemLabelHeadingTag }}>
                                            @endif

                                            @if ($cloneActionIsVisible || $deleteActionIsVisible || $visibleExtraItemActions)
                                                <ul
                                                    class="fi-fo-repeater-item-header-end-actions"
                                                >
                                                    @foreach ($visibleExtraItemActions as $extraItemAction)
                                                        <li x-on:click.stop>
                                                            {{ $extraItemAction(['item' => $itemKey]) }}
                                                        </li>
                                                    @endforeach

                                                    @if ($cloneActionIsVisible)
                                                        <li x-on:click.stop>
                                                            {{ $cloneAction }}
                                                        </li>
                                                    @endif

                                                    @if ($deleteActionIsVisible)
                                                        <li x-on:click.stop>
                                                            {{ $deleteAction }}
                                                        </li>
                                                    @endif
                                                </ul>
                                            @endif
                                        </div>
                                    @endif

                                    <div
                                        class="fi-fo-repeater-item-content"
                                    >
                                        {{ $item }}
                                    </div>
                                </li>

                            @endforeach
                        </ul>
                    @endif
                </div>
            </x-dynamic-component>
        </div>
    </div>
</div>
