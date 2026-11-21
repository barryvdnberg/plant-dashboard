@php
    use Filament\Actions\Action;

    $items = $getItems();
    $labels = [];
    foreach ($getTabDefinitions() as $key => $definition) {
        if (! $definition->isHidden()) {
            $labels[$key] = (string) $getItemLabel($key);
        }
    }
    $enabledItemKey = array_key_exists($getEnabledItemName() ?? '', $items)
        ? $getEnabledItemName()
        : array_key_first($items);
    $addAction = $getAction($getAddActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $extraItemActions = $getExtraItemActions();
    $emptySchema = $getEmptySchema();
    $id = $getId();
@endphp

<div
    x-data="{
        selectedItemKey: @js($enabledItemKey),
        labels: @js($labels),
        items: $wire.entangle(@js($getStatePath())),
        heightRevision: 0,
        cleanups: [],
        isLgUp: window.matchMedia('(min-width: 1024px)').matches,
        get keys() {
            return Object.keys(this.items ?? {}).filter(key => Object.hasOwn(this.labels, key))
        },
        tabId(key) { return @js($id . '-tab-') + key },
        panelId(key) { return @js($id . '-panel-') + key },
        holdHeight() {
            const root = this.$refs.tabsRoot
            const revision = ++this.heightRevision
            root.style.minHeight = `${root.getBoundingClientRect().height}px`
            return () => this.$nextTick(() => requestAnimationFrame(() => requestAnimationFrame(() => {
                if (revision === this.heightRevision) root.style.removeProperty('min-height')
            })))
        },
        selectTab(key, event) {
            const release = this.holdHeight()
            this.selectedItemKey = key
            release()
            this.$nextTick(() => event?.currentTarget?.focus({ preventScroll: true }))
        },
        navigate(event) {
            const next = this.isLgUp ? 'ArrowDown' : 'ArrowRight'
            const previous = this.isLgUp ? 'ArrowUp' : 'ArrowLeft'
            if (! [next, previous, 'Home', 'End'].includes(event.key)) return
            event.preventDefault()
            const keys = this.keys
            let index = keys.indexOf(this.selectedItemKey)
            if (event.key === 'Home') index = 0
            else if (event.key === 'End') index = keys.length - 1
            else index = (index + (event.key === next ? 1 : -1) + keys.length) % keys.length
            if (keys[index]) {
                this.selectTab(keys[index])
                this.$nextTick(() => document.getElementById(this.tabId(keys[index]))?.focus({ preventScroll: true }))
            }
        },
        init() {
            const mq = window.matchMedia('(min-width: 1024px)')
            const update = () => { this.isLgUp = mq.matches }
            mq.addEventListener('change', update)
            this.cleanups.push(() => mq.removeEventListener('change', update))
            this.$watch('keys', (keys, previous) => {
                const added = keys.find(key => ! (previous ?? []).includes(key))
                if (added || ! keys.includes(this.selectedItemKey)) {
                    this.selectTab(added ?? keys[0] ?? null)
                }
            })
            this.cleanups.push(this.$wire.$interceptMessage(({ onSuccess }) => {
                onSuccess(({ onMorphed }) => {
                    const release = this.holdHeight()
                    onMorphed(release)
                })
            }))
        },
        destroy() {
            ++this.heightRevision
            this.cleanups.forEach(cleanup => cleanup())
        },
    }"
    {{ $attributes->merge(['id' => $id], escape: false)->merge($getExtraAttributes(), escape: false) }}
>
    <x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
        <div class="fi-fo-repeater fi-fo-field-content-col">
            <div
                wire:ignore.self
                class="fi-sc-tabs fi-contained"
                x-ref="tabsRoot"
                x-bind:class="{ 'fi-vertical': isLgUp }"
                style="overflow-anchor: none;"
            >
                <div
                    class="fi-tabs fi-contained"
                    x-bind:class="{ 'fi-vertical': isLgUp }"
                    x-bind:style="isLgUp ? 'min-width: 200px; padding: 0; justify-content: space-between' : 'min-width: 0; padding: 0'"
                >
                    {{-- check if there are any items otherwise dont display this part --}}
                    @if ($items && count($items) > 0)
                    <div class="fi-fo-repeater-item-has-header">
                        @if ($hasInternalLabel())
                            <div class="fi-fo-repeater-item-header" x-show="isLgUp">{{ $getLabel() }}</div>
                        @endif
                        <div class="fi-tabs fi-contained no-borders" x-bind:class="{ 'fi-vertical': isLgUp }">
                            <div role="tablist" aria-label="{{ $getLabel() ?? $getName() }}" x-bind:aria-orientation="isLgUp ? 'vertical' : 'horizontal'" style="display: contents;" x-on:keydown="navigate($event)">
                                <template x-for="key in keys" :key="key">
                                    <button
                                        type="button"
                                        class="fi-tabs-item"
                                        role="tab"
                                        x-bind:id="tabId(key)"
                                        x-bind:aria-controls="panelId(key)"
                                        x-bind:aria-selected="(selectedItemKey === key).toString()"
                                        x-bind:tabindex="selectedItemKey === key ? 0 : -1"
                                        x-bind:class="{ 'fi-active': selectedItemKey === key }"
                                        x-on:click="selectTab(key, $event)"
                                    ><span class="fi-tabs-item-label" x-text="labels[key]"></span></button>
                                </template>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if ($addAction->isVisible())
                        <div class="fi-tabs fi-contained no-borders">{{ $addAction }}</div>
                    @endif
                </div>

                @foreach ($items as $itemKey => $item)
                    @php
                        $visibleExtraItemActions = array_filter($extraItemActions, fn (Action $action): bool => $action(['item' => $itemKey])->isVisible());
                        $deleteActionForItem = $deleteAction(['item' => $itemKey]);
                        $showDelete = $deleteActionForItem->isVisible();
                    @endphp
                    <div
                        wire:ignore.self
                        wire:key="{{ $item->getLivewireKey() }}.item"
                        role="tabpanel"
                        x-bind:id="panelId(@js($itemKey))"
                        x-bind:aria-labelledby="tabId(@js($itemKey))"
                        x-show="selectedItemKey === @js($itemKey)"
                        x-bind:class="{ 'fi-active': selectedItemKey === @js($itemKey) }"
                        x-cloak
                        class="fi-sc-tabs-tab"
                        style="margin: 0; padding: 0;"
                    >
                        <div class="fi-fo-repeater-item-has-header">
                            <div class="fi-fo-repeater-item-header">
                                <h3 class="fi-fo-repeater-item-header-label">{{ $getItemLabel($itemKey) }}</h3>
                                @if ($showDelete || $visibleExtraItemActions)
                                    <ul class="fi-fo-repeater-item-header-end-actions">
                                        @foreach ($visibleExtraItemActions as $extraItemAction)
                                            <li>{{ $extraItemAction(['item' => $itemKey]) }}</li>
                                        @endforeach
                                        @if ($showDelete)
                                            <li>{{ $deleteActionForItem }}</li>
                                        @endif
                                    </ul>
                                @endif
                            </div>
                            <div class="fi-fo-repeater-item-content" style="padding: calc(var(--spacing) * 6);">{{ $item }}</div>
                        </div>
                    </div>
                @endforeach

                @if ($emptySchema)
                    <div x-show="keys.length === 0" x-bind:class="{ 'fi-active': keys.length === 0 }" class="fi-sc-tabs-tab" style="padding: calc(var(--spacing) * 5);">{{ $emptySchema }}</div>
                @endif
            </div>
        </div>
    </x-dynamic-component>
</div>
