<div class="space-y-6">

    {{-- ── Summary strip ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
        {{-- Total plants --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Total plants') }}</p>
            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $plants->count() }}</p>
        </div>

        {{-- At-risk count --}}
        <div class="rounded-xl border {{ $atRiskPlants->count() > 0 ? 'border-danger-200 bg-danger-50 dark:border-danger-700 dark:bg-danger-950' : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900' }} p-5">
            <p class="text-xs font-medium uppercase tracking-wide {{ $atRiskPlants->count() > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-500 dark:text-gray-400' }}">{{ __('At risk') }}</p>
            <p class="mt-1 text-3xl font-bold {{ $atRiskPlants->count() > 0 ? 'text-danger-700 dark:text-danger-300' : 'text-gray-900 dark:text-white' }}">{{ $atRiskPlants->count() }}</p>
        </div>

        {{-- Threshold --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Current threshold') }}</p>
            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ __('Zone :zone', ['zone' => $threshold?->getLabel() ?? '—']) }}</p>
        </div>
    </div>

    {{-- ── At-risk plants ──────────────────────────────────────────────────── --}}
    @if($atRiskPlants->isNotEmpty())
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Winter hardiness warnings') }}</h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('These plants have a hardiness zone below the current threshold and may struggle this winter.') }}
                </p>
            </div>

            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($atRiskPlants->sortByDesc('diff') as $plant)
                    @php
                        $colors = match($plant['severity']) {
                            'critical' => ['bg' => 'bg-danger-50 dark:bg-danger-950', 'border' => 'border-l-4 border-l-danger-500', 'badge' => 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300', 'icon' => 'text-danger-500'],
                            'danger'   => ['bg' => 'bg-orange-50 dark:bg-orange-950', 'border' => 'border-l-4 border-l-orange-500', 'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300', 'icon' => 'text-orange-500'],
                            default    => ['bg' => 'bg-warning-50 dark:bg-warning-950', 'border' => 'border-l-4 border-l-warning-400', 'badge' => 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300', 'icon' => 'text-warning-500'],
                        };
                        $label = match($plant['severity']) {
                            'critical' => __('Critical'),
                            'danger'   => __('High risk'),
                            default    => __('Warning'),
                        };
                    @endphp
                    <li class="flex items-center gap-4 px-6 py-4 {{ $colors['bg'] }} {{ $colors['border'] }}">
                        {{-- Icon --}}
                        <div class="shrink-0">
                            @if($plant['severity'] === 'critical')
                                <x-heroicon-s-x-circle class="h-6 w-6 {{ $colors['icon'] }}" />
                            @elseif($plant['severity'] === 'danger')
                                <x-heroicon-s-exclamation-circle class="h-6 w-6 {{ $colors['icon'] }}" />
                            @else
                                <x-heroicon-s-exclamation-triangle class="h-6 w-6 {{ $colors['icon'] }}" />
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $plant['name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('Zone :zone', ['zone' => $plant['zone']?->getLabel() ?? '—']) }}
                                &mdash;
                                {{ __(':n zones below threshold', ['n' => $plant['diff']]) }}
                            </p>
                        </div>

                        {{-- Severity badge --}}
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colors['badge'] }}">
                            {{ $label }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        {{-- All clear card --}}
        <div class="rounded-xl border border-success-200 bg-success-50 p-6 dark:border-success-700 dark:bg-success-950">
            <div class="flex items-center gap-3">
                <x-heroicon-s-check-circle class="h-6 w-6 text-success-500" />
                <div>
                    <p class="text-sm font-semibold text-success-800 dark:text-success-300">{{ __('All plants are winter-hardy') }}</p>
                    <p class="text-sm text-success-700 dark:text-success-400">{{ __('No plants are below the current hardiness threshold.') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ── All plants list ─────────────────────────────────────────────────── --}}
    @if($plants->isNotEmpty())
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('All plants') }}</h2>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($plants->sortBy('name') as $plant)
                    <li class="flex items-center gap-4 px-6 py-3">
                        {{-- Status dot --}}
                        <span class="inline-block h-2 w-2 shrink-0 rounded-full {{ match($plant['severity']) {
                            'critical' => 'bg-danger-500',
                            'danger'   => 'bg-orange-500',
                            'warning'  => 'bg-warning-400',
                            default    => 'bg-success-400',
                        } }}"></span>

                        <span class="flex-1 text-sm text-gray-800 dark:text-gray-200">{{ $plant['name'] }}</span>

                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $plant['zone'] ? __('Zone :zone', ['zone' => $plant['zone']->getLabel()]) : __('No zone') }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

</div>
