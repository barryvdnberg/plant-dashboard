<div>
    @if($plants->isNotEmpty())
        <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-700 dark:bg-warning-950">
            <div class="flex items-start gap-3">
                <x-filament::icon
                    icon="heroicon-o-exclamation-triangle"
                    class="mt-0.5 h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400"
                />
                <div class="flex-1 space-y-2">
                    <p class="text-sm font-semibold text-warning-800 dark:text-warning-300">
                        {{ __('Winter hardiness warning') }}
                    </p>
                    <p class="text-sm text-warning-700 dark:text-warning-400">
                        {{ __('The following plants have a winter hardiness zone below zone :zone and may not survive current conditions:', ['zone' => $threshold]) }}
                    </p>
                    <ul class="mt-1 list-inside list-disc space-y-1 text-sm text-warning-700 dark:text-warning-400">
                        @foreach($plants as $gardenPlant)
                            <li>
                                <span class="font-medium">{{ $gardenPlant->name ?: $gardenPlant->cultivar?->name }}</span>
                                @php $zone = $gardenPlant->cultivar?->environment?->winter_hardiness; @endphp
                                @if($zone)
                                    &mdash; {{ __('Zone :zone', ['zone' => $zone->getLabel()]) }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>
