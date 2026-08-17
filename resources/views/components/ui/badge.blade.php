@props(['color' => 'stone'])

@php
    $colors = [
        'green' => 'bg-green-100 text-green-800',
        'amber' => 'bg-amber-100 text-amber-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'red' => 'bg-red-100 text-red-700',
        'stone' => 'bg-stone-100 text-stone-700',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', $colors[$color] ?? $colors['stone']]) }}>
    {{ $slot }}
</span>
