@php
    use Filament\Actions\Action;
    use Filament\Support\Enums\Alignment;
    use Illuminate\View\ComponentAttributeBag;

    $fieldWrapperView = $getFieldWrapperView();

    $items = $getItems();
    $enabledItemName = $getEnabledItemName();

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
    $hasHiddenOwnAddAction = $hasHiddenOwnAddAction();

    $key = $getKey();

    $itemLabelHeadingTag = $getHeadingTag();
    $isItemLabelTruncated = $isItemLabelTruncated();
    $labelBetweenItems = $getLabelBetweenItems();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-data="{ enabledItem: @js($enabledItemName) }"
        x-on:select-repeater-toggle.window="$event.detail.name === '{{ $getName() }}' && (enabledItem = $event.detail.item)"
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
                        // $deleteActionIsVisible = $isDeletable && $deleteAction->isVisible();
                        $deleteActionIsVisible = $isDeletable && $itemLabel !== 'default';
                        $hasItemHeader = $hasItemHeaders && (filled($itemLabel) || $cloneActionIsVisible || $deleteActionIsVisible || $visibleExtraItemActions);
                    @endphp

                    <li
                        wire:ignore.self
                        wire:key="{{ $item->getLivewireKey() }}.item"
                        x-sortable-item="{{ $itemKey }}"
                        x-show="enabledItem === @js($itemLabel)"
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

        @if ($isAddable && (! $hasHiddenOwnAddAction) && $addAction->isVisible())
            <div
                @class([
                    'fi-fo-repeater-add',
                    ($addActionAlignment instanceof Alignment) ? ('fi-align-' . $addActionAlignment->value) : $addActionAlignment,
                ])
            >
                {{ $addAction }}
            </div>
        @endif
    </div>
</x-dynamic-component>
