@php

    $fieldWrapperView = $getFieldWrapperView();

    $repeaterName = $getRepeaterName();
    $repeaterStatePath = $getRepeaterStatePath();
    $labels = $getLabels();
    $default = $getDefaultItem();
    $secondary = $isSecondary();
    $addAction = $getRepeaterAddAction();

@endphp

@props([
    'secondary' => false,
])

<div class="fi-sc-section">
    <section
        x-data="{
            enabledItem: @js($default),
            labels: @js($labels),
            items: $wire.entangle(@js($repeaterStatePath)),
            get options() {
                const values = Object.values(this.items ?? {});
                const groups = [];

                for (const item of values) {
                    const group = item?.group;

                    if (! group || groups.includes(group)) {
                        continue;
                    }

                    groups.push(group);
                }

                return groups.map((group) => this.labels[group] ?? group);
            },
        }"
        x-on:select-repeater-toggle.window="$event.detail.name === '{{ $repeaterName }}' && (enabledItem = $event.detail.item)"
        {{
            $attributes->class([
                'fi-section',
                'fi-secondary' => $secondary,
            ])
        }}
    >
    <div class="fi-section-content-ctn">
        <div class="fi-sc fi-sc-has-gap fi-sc-dense fi-grid lg:fi-grid-cols fi-section-content" style="--cols-lg: repeat(1, minmax(0, 1fr)); --cols-default: repeat(1, minmax(0, 1fr));">
            <template x-for="option in options" :key="option">
                <x-filament::button color="gray"
                    wire:click="
                        enabledItem = option;
                        $dispatch('select-repeater-toggle', { name: '{{ $repeaterName }}', item: option });
                    "
                    tag="div"
                    x-bind:class="enabledItem === option ? 'fi-color fi-color-primary fi-bg-color-400 hover:fi-bg-color-300 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-900 hover:fi-text-color-800 dark:fi-text-color-950 dark:hover:fi-text-color-950' : ''"
                    x-text="option"
                ></x-filament::button>
            </template>
            @if ($addAction && $addAction->isVisible())
                <div>
                    {{ $addAction }}
                </div>
            @endif
        </div>
    </div>
    </section>
</div>

