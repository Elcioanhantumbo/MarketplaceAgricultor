@props(['title', 'subtitle' => null])

<div {{ $attributes->class('mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between') }}>
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-stone-900 sm:text-2xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-stone-500">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 gap-2">{{ $actions }}</div>
    @endisset
</div>
