@props(['title' => 'Nada por aqui ainda', 'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375C2.754 3.75 2.25 4.254 2.25 4.875v1.5c0 .621.504 1.125 1.125 1.125Z'])

<div {{ $attributes->class('rounded-xl border border-dashed border-stone-300 bg-stone-50/60 px-6 py-10 text-center') }}>
    <svg class="mx-auto h-9 w-9 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}" />
    </svg>
    <p class="mt-3 text-sm font-medium text-stone-600">{{ $title }}</p>
    @isset($slot)
        @if (trim($slot))
            <p class="mt-1 text-sm text-stone-400">{{ $slot }}</p>
        @endif
    @endisset
</div>
