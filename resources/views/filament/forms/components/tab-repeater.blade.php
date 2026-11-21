@php
    use Filament\Actions\Action;

    $fieldWrapperView = $getFieldWrapperView();

    $items = $getItems();

    $enabledKeyFieldValue = $getEnabledItemName();

    $repeaterStatePath = $getStatePath();

    $emptySchema = $getEmptySchema();

    $label = $getLabel();
    $hasInternalLabel = $hasInternalLabel();
    $labels = $getOptions();

    $addAction = $getAction($getAddActionName());
    $editAction = $getAction($getEditActionName());
    $cloneAction = $getAction($getCloneActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $extraItemActions = $getExtraItemActions();

    $isAddable = $isAddable();
    $isCloneable = $isCloneable();
    $isDeletable = $isDeletable();
    $isEditable = $isEditable();

    $hasItemNumbers = $hasItemNumbers();
    $hasItemHeaders = $hasItemHeaders();

    $itemLabelHeadingTag = $getHeadingTag();
    $isItemLabelTruncated = $isItemLabelTruncated();

    // Select whichever existing item currently has the enabled key-field value,
    // falling back to the first item, so the selection is tracked by stable item
    // key rather than the item's (editable) key-field value.
    $enabledItemKey = null;

    foreach ($items as $itemKey => $item) {
        if (($item->getRawState()[$getKeyFieldName()] ?? null) === $enabledKeyFieldValue) {
            $enabledItemKey = $itemKey;

            break;
        }
    }

    $enabledItemKey ??= array_key_first($items);

    $tabIdPrefix = $getId() . '-tab';
    $panelIdPrefix = $getId() . '-panel';
@endphp

<div
    x-data="{
        selectedItemKey: @js($enabledItemKey),
        heightRevision: 0,
        cleanups: [],
        labels: @js($labels),
        keyFieldName: @js($getKeyFieldName()),
        items: $wire.entangle(@js($repeaterStatePath)),
        get options() {
            const seenKeyFieldValues = new Set()
            const options = []

            for (const [itemKey, item] of Object.entries(this.items ?? {})) {
                const keyFieldValue = item?.[this.keyFieldName]

                if (! keyFieldValue || seenKeyFieldValues.has(keyFieldValue)) {
                    continue
                }

                seenKeyFieldValues.add(keyFieldValue)
                options.push({ key: itemKey, keyFieldValue })
            }

            return options
        },
        tabId(key) {
            return @js($tabIdPrefix) + '-' + key
        },
        panelId(key) {
            return @js($panelIdPrefix) + '-' + key
        },
        holdHeight() {
            const root = this.$refs.tabsRoot
            const revision = ++this.heightRevision

            // Apply synchronously, before Alpine hides or Livewire removes a panel.
            root.style.minHeight = `${root.getBoundingClientRect().height}px`

            return () => this.$nextTick(() => {
                // x-show reveals the replacement on the next animation frame.
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        if (revision === this.heightRevision) {
                            root.style.removeProperty('min-height')
                        }
                    })
                })
            })
        },
        withStableHeight(callback) {
            const releaseHeight = this.holdHeight()
            callback()
            releaseHeight()
        },
        selectTab(key, event) {
            const tab = event?.currentTarget

            this.withStableHeight(() => { this.selectedItemKey = key })
            this.$nextTick(() => { tab?.focus({ preventScroll: true }) })
        },
        isLgUp: window.matchMedia('(min-width: 1024px)').matches,
        init() {
            const mq = window.matchMedia('(min-width: 1024px)')
            const updateBreakpoint = () => { this.isLgUp = mq.matches }
            updateBreakpoint()
            mq.addEventListener('change', updateBreakpoint)
            this.cleanups.push(() => mq.removeEventListener('change', updateBreakpoint))

            this.$watch('items', (items) => {
                const keys = Object.keys(items ?? {})

                if (! keys.includes(this.selectedItemKey)) {
                    this.withStableHeight(() => { this.selectedItemKey = keys[0] ?? null })
                }
            })

            // Filament partials use Alpine.morph directly, bypassing Livewire's
            // DOM hooks. Hold the height before the response updates any state.
            this.cleanups.push(this.$wire.$interceptMessage(({ onSuccess }) => {
                onSuccess(({ onMorphed }) => {
                    const releaseHeight = this.holdHeight()
                    onMorphed(releaseHeight)
                })
            }))
        },
        destroy() {
            ++this.heightRevision
            this.cleanups.forEach(cleanup => cleanup())
        },
    }"
    {{
        $attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)
    }}
>
    <x-dynamic-component :component="$fieldWrapperView" :field="$field">
        <div
            {{
                $attributes
                    ->merge($getExtraAttributes(), escape: false)
                    ->class([
                        // we have to add fi-fo-field-content-col to have the
                        // fi-sc-tabs believe it is inside the form not inside the repeater
                        'fi-fo-repeater fi-fo-field-content-col',
                    ])
            }}
        >
            <div
                wire:ignore.self
                class="fi-sc-tabs fi-contained fi-vertical"
                x-ref="tabsRoot"
                x-bind:class="{ 'fi-vertical': isLgUp }"
                style="overflow-anchor: none;"
            >
                <div x-ref="tabsHeader" class="fi-tabs fi-contained" x-bind:class="{ 'fi-vertical': isLgUp }" x-bind:style="isLgUp ? 'min-width: 200px; padding: 0px; justify-content: space-between' : 'min-width: 0; padding: 0px;'" x-cloak>
                    @if($hasInternalLabel)
                    <div class="fi-fo-repeater-item-has-header">
                        <div class="fi-fo-repeater-item-header" x-show="isLgUp">
                            {{ $label }}
                        </div>
                        <div x-bind:class="{ 'fi-fo-repeater-item-content': isLgUp }" style="padding: 0px;">
                    @endif
                    
                            <div class="fi-tabs fi-contained no-borders" x-bind:class="{ 'fi-vertical': isLgUp }" x-show="options.length > 0 || !isLgUp">
                                <div
                                    class="fi-sc-tabs-tablist"
                                    role="tablist"
                                    x-bind:aria-orientation="isLgUp ? 'vertical' : null"
                                    aria-label="{{ e($getLabel() ?? $getName()) }}"
                                    style="display: contents;"
                                >
                                    <template x-for="option in options" :key="option.key">
                                        <button
                                            type="button"
                                            class="fi-tabs-item"
                                            role="tab"
                                            x-bind:id="tabId(option.key)"
                                            x-bind:aria-controls="panelId(option.key)"
                                            x-bind:aria-selected="(selectedItemKey === option.key).toString()"
                                            x-bind:tabindex="selectedItemKey === option.key ? 0 : -1"
                                            x-on:click="selectTab(option.key, $event)"
                                            x-bind:class="{ 'fi-active': selectedItemKey === option.key }"
                                        >
                                            <span
                                                class="fi-tabs-item-label"
                                                x-text="labels[option.keyFieldValue] ?? option.keyFieldValue"
                                            ></span>
                                        </button>
                                    </template>
                                    
                                    @if ($addAction && $isAddable)
                                        <div x-show="!isLgUp">
                                            {{ $addAction(['item' => 'default']) }}
                                        </div>

                                    @endif
                                </div>
                            </div>
                    
                            @if($hasInternalLabel)
                        </div>
                    </div>
                    @endif

                    @if ($addAction && $isAddable)
                    <div class="fi-tabs fi-contained fi-vertical no-borders" x-show="isLgUp">
                        {{ $addAction(['item' => 'default']) }}
                    </div>
                    @endif
                </div>

                @foreach ($items as $itemKey => $item)
                    @php
                        $itemLabel = $getItemLabel($itemKey, $loop->index);

                        $visibleExtraItemActions = array_filter(
                            $extraItemActions,
                            fn (Action $action): bool => $action(['item' => $itemKey])->isVisible(),
                        );

                        $cloneActionForItem = $cloneAction(['item' => $itemKey]);
                        $cloneActionIsVisible = $isCloneable && $cloneActionForItem->isVisible();

                        $deleteActionForItem = $deleteAction(['item' => $itemKey]);
                        $deleteActionIsVisible = $isDeletable && $isItemDeletable($itemKey);

                        $editActionForItem = $editAction(['item' => $itemKey]);
                        $editActionIsVisible = $isEditable && $isItemEditable($itemKey);

                        $hasItemHeader = $hasItemHeaders &&
                            (filled($itemLabel) || $cloneActionIsVisible || $deleteActionIsVisible || $editActionIsVisible || $visibleExtraItemActions);
                    @endphp

                    <div
                        wire:ignore.self
                        wire:key="{{ $item->getLivewireKey() }}.item"
                        x-bind:id="panelId(@js($itemKey))"
                        role="tabpanel"
                        x-bind:aria-labelledby="tabId(@js($itemKey))"
                        x-show="selectedItemKey === @js($itemKey)"
                        x-cloak
                        x-bind:class="{ 'fi-active': selectedItemKey === @js($itemKey) }"
                        class="fi-sc-tabs-tab"
                        style="margin: 0; padding: 0;"
                    >
                        <div
                            @class([
                                'fi-fo-repeater-item-has-header' => $hasItemHeader,
                            ])
                        >
                            @if ($hasItemHeader)
                                <div class="fi-fo-repeater-item-header">
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

                                    @if ($cloneActionIsVisible || $deleteActionIsVisible || $editActionIsVisible || $visibleExtraItemActions)
                                        <ul class="fi-fo-repeater-item-header-end-actions">
                                            @foreach ($visibleExtraItemActions as $extraItemAction)
                                                <li x-on:click.stop>
                                                    {{ $extraItemAction(['item' => $itemKey]) }}
                                                </li>
                                            @endforeach

                                            @if ($cloneActionIsVisible)
                                                <li x-on:click.stop>
                                                    {{ $cloneActionForItem }}
                                                </li>
                                            @endif

                                            @if ($editActionIsVisible)
                                                <li x-on:click.stop>
                                                    {{ $editActionForItem }}
                                                </li>
                                            @endif

                                            @if ($deleteActionIsVisible)
                                                <li x-on:click.stop>
                                                    {{ $deleteActionForItem }}
                                                </li>
                                            @endif
                                        </ul>
                                    @endif
                                </div>
                            @endif

                            <div class="fi-fo-repeater-item-content" style="padding: calc(var(--spacing) * 6);">
                                {{ $item }}
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- for when there are no items we show the empty schema --}}
                @if ($emptySchema)
                    
                    <div x-bind:class="{ 'fi-active': Object.keys(items).length === 0 }" class="fi-sc-tabs-tab" style="padding: calc(var(--spacing) * 5);">
                        {{ $emptySchema }}
                    </div>
                @endif

            </div>
        </div>
    </x-dynamic-component>
</div>

