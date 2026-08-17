@props(['type' => 'info'])

@php
    $styles = [
        'success' => ['bg-green-50 border-green-200 text-green-800', 'text-green-500'],
        'error' => ['bg-red-50 border-red-200 text-red-700', 'text-red-500'],
        'warning' => ['bg-amber-50 border-amber-200 text-amber-800', 'text-amber-500'],
        'info' => ['bg-blue-50 border-blue-200 text-blue-800', 'text-blue-500'],
    ];
    [$box, $iconColor] = $styles[$type] ?? $styles['info'];

    $icons = [
        'success' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'error' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        'warning' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        'info' => 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
    ];
@endphp

<div {{ $attributes->class(['flex gap-2.5 rounded-lg border px-4 py-3 text-sm', $box]) }}>
    <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icons[$type] ?? $icons['info'] }}" />
    </svg>
    <div>{{ $slot }}</div>
</div>
