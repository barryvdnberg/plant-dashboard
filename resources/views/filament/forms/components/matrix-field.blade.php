@php
    use Illuminate\View\ComponentAttributeBag;
    use Filament\Support\Enums\Size;

    $fieldWrapperView = $getFieldWrapperView();
    $statePath = $getStatePath();

    $columns = $getButtonColumns();
    $rows = $getButtonRows();
    $totalButtons = $columns * $rows;

    $initialValues = array_fill(0, $totalButtons, 0);

    $colors = $getColors();
    
    $sizes = [
        Size::ExtraSmall->value => '2rem',  // 32px
        Size::Small->value      => '2.5rem',// 40px
        Size::Medium->value     => '3rem',  // 48px
        Size::Large->value      => '3.5rem',// 56px
        Size::ExtraLarge->value => '4rem',  // 64px
    ];

    $cellSize = $sizes[$getSize()->value] ?? $sizes[Size::Medium->value];
    $maxMatrixWidth = "calc({$cellSize} * {$columns})";
    // $maxWidth = $cellSize * $columns;

    $columnLabels = $getColumnLabels();
    $rowLabels = $getRowLabels();

    $hasColumnLabels = filled($columnLabels);
    $hasRowLabels = filled($rowLabels);
@endphp


<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>

    
    <div class="fi-grid" style="
        grid-template-columns: minmax(0, {{ $maxMatrixWidth }}){{ $hasRowLabels ? ' auto' : '' }};
        grid-template-rows: {{ $hasColumnLabels ? 'auto ' : '' }}1fr;
        {{ $hasRowLabels ? ' grid-column-gap: 4px' : ''}}">
        @if ($hasColumnLabels)
            <div class="fi-grid" style="grid-template-columns: repeat({{ count($columnLabels) }}, 1fr);">
                @foreach ($columnLabels as $label)
                    <div style="text-align: center;">
                        {{ $label }}
                    </div>
                @endforeach
            </div>     
        @endif
        @if ($hasRowLabels && $hasColumnLabels)
            <div></div>
        @endif
    <div
        wire:ignore
        x-data="{
            values: @js($getState() ?? $initialValues),
            colors: @js($colors),
            swapValue(idx) {
                this.values[idx] = (Number(this.values[idx] ?? 0) + 1) % Object.keys(this.colors).length
            },
        }"
        x-modelable="values"
        {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
        style="display: grid; 
            grid-template-columns: repeat({{ $columns }}, 1fr); 
            grid-template-rows: repeat({{ $rows }}, 1fr); 
            gap: 0px;"
    >
        <template x-for="(val, idx) in values" :key="idx">
            <x-filament::button
                type="button"
                x-bind:class="['grid-btn', colors[val]]"
                @click="swapValue(idx)"
                tabindex="0"
            ></x-filament::button>
        </template>
    </div>
        @if ($hasRowLabels)
            <div class="fi-grid" style="grid-template-rows: repeat({{ count($rowLabels) }}, 1fr);">
                @foreach ($rowLabels as $label)
                    <div style="display: grid; place-items: center start; text-align: left;">
                        {{ $label }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <style>
        .grid-btn {
            border: 1px solid #888;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            aspect-ratio: 1 / 1;
            outline: none;
            border-radius: 0;
            transition: box-shadow 0.1s;
            font-size: 0;
        }
        .grid-btn:focus {
            box-shadow: 0 0 0 2px #1976D2;
            z-index: 1;
        }
    </style>
</x-dynamic-component>