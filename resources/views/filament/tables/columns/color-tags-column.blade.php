<div {{ $getExtraAttributeBag() }}>
    @php
        $colors = collect($getState());
    @endphp

    <div class="fi-size-sm fi-ta-text-item fi-ta-text">
        @if ($colors->isNotEmpty())
            <div class="flex flex-wrap gap-1">
                @foreach ($colors as $color)
                    @if ($color->id && ($color->translation || $color->name))
                        <x-filament::badge color="{{ \App\Support\ColorTag::name($color->name) }}">
                            {{ $color->translation ?? $color->name }}
                        </x-filament::badge>
                    @endif
                @endforeach
            </div>
        @else
            <span class="text-gray-400 text-xs italic">{{ __('No :name', ['name' => __('attributes.Colors')]) }}</span>
        @endif
    </div>
    
</div>
