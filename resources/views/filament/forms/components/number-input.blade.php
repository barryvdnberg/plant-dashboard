<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div>
        <input
            type="text"
            {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
            {!! $attributes->merge([
                'class' => 'custom-input-class ' . ($errors->has($getStatePath()) ? 'border-red-500' : ''),
                'placeholder' => $getPlaceholder(),
            ]) !!}
        >

        @if ($getHelperText())
            <p class="text-sm text-gray-500">{{ $getHelperText() }}</p>
        @endif
    </div>

    @push('styles')
    <style>
    .custom-input-class {
        border: 2px solid #4F46E5;
        border-radius: 6px;
        padding: 0.5rem 1rem;
        /* Add your custom CSS here */
    }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Add your custom JS functionality here, for example:
        document.addEventListener('DOMContentLoaded', function () {
            // Example: focus effect
            document.querySelectorAll('.custom-input-class').forEach(function(input) {
                input.addEventListener('focus', function() {
                    input.style.backgroundColor = '#eef2ff';
                });
                input.addEventListener('blur', function() {
                    input.style.backgroundColor = '';
                });
            });
        });
    </script>
    @endpush
</x-dynamic-component>
