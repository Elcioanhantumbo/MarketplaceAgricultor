@props(['name' => null, 'label' => null, 'hint' => null, 'required' => false])

@php
    $hasError = $name && $errors->has($name);
@endphp

<div {{ $attributes }}>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-stone-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($hasError)
        <p class="mt-1.5 text-sm text-red-600">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs text-stone-400">{{ $hint }}</p>
    @endif
</div>
