@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->class('rounded-xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6') }}>
    @if ($title || $subtitle || isset($actions))
        <div class="mb-4 flex items-start justify-between gap-4">
            <div>
                @if ($title)
                    <h2 class="text-sm font-semibold text-stone-900">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-stone-500">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="shrink-0">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</div>
