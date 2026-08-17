@props(['variant' => 'primary', 'size' => 'md'])

@php
    $base = 'inline-flex items-center justify-center gap-1.5 rounded-lg font-medium transition disabled:cursor-not-allowed disabled:opacity-50 focus:outline-none focus:ring-2';

    $variants = [
        'primary' => 'bg-green-700 text-white shadow-sm hover:bg-green-800 focus:ring-green-600/30',
        'secondary' => 'border border-stone-300 bg-white text-stone-700 shadow-sm hover:border-stone-400 hover:bg-stone-50 focus:ring-stone-400/30',
        'danger' => 'border border-stone-300 bg-white text-red-600 shadow-sm hover:border-red-400 hover:bg-red-50 focus:ring-red-500/30',
        'ghost' => 'text-stone-600 hover:bg-stone-100 hover:text-stone-900 focus:ring-stone-400/30',
        'link' => 'text-green-700 hover:underline',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($variant === 'link' ? '' : ($sizes[$size] ?? $sizes['md']));
@endphp

@if ($attributes->has('href'))
    <a {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
