<div>
    <x-ui.card>
        <div class="flex items-center gap-4">
            @if ($producer->user->profile?->avatar_path)
                <img src="{{ asset('storage/'.$producer->user->profile->avatar_path) }}" class="h-16 w-16 rounded-full object-cover ring-2 ring-stone-100" alt="">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-stone-100 text-lg font-medium text-stone-400">
                    {{ strtoupper(substr($producer->business_name ?: $producer->user->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h1 class="text-xl font-semibold text-stone-900">{{ $producer->business_name ?: $producer->user->name }}</h1>
                @if ($producer->business_name)
                    <p class="text-sm text-stone-500">{{ $producer->user->name }}</p>
                @endif
                <p class="mt-1 text-sm">
                    @if ($averageRating)
                        <span class="text-amber-500">{{ str_repeat('★', round($averageRating)) }}{{ str_repeat('☆', 5 - round($averageRating)) }}</span>
                        <span class="text-stone-500">{{ number_format($averageRating, 1) }} ({{ $reviewsCount }} {{ $reviewsCount === 1 ? 'avaliação' : 'avaliações' }})</span>
                    @else
                        <span class="text-stone-400">Ainda sem avaliações</span>
                    @endif
                </p>
            </div>
        </div>

        @if ($bio = $producer->user->profile?->bio)
            <p class="mt-4 border-t border-stone-100 pt-4 text-sm text-stone-600">{{ $bio }}</p>
        @endif

        @if ($district = $producer->user->profile?->district)
            <p class="mt-2 flex items-center gap-1.5 text-sm text-stone-500">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                {{ collect([$district, $producer->user->profile?->province])->filter()->implode(', ') }}
            </p>
        @endif
    </x-ui.card>

    <div class="mt-6">
        <h2 class="mb-3 text-sm font-semibold text-stone-900">Ofertas disponíveis</h2>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @forelse ($listings as $listing)
                <a href="{{ route('ofertas.show', $listing) }}" wire:navigate class="rounded-xl border border-stone-200 bg-white p-4 text-sm shadow-sm transition hover:border-green-600 hover:shadow-md">
                    <p class="font-medium text-stone-900">{{ $listing->product->name }}</p>
                    <p class="mt-1 text-stone-500">{{ $listing->quantity }} {{ $listing->unit }} · {{ number_format((float) $listing->price, 2) }} MZN</p>
                </a>
            @empty
                <div class="sm:col-span-2">
                    <x-ui.empty-state title="Sem ofertas disponíveis de momento" />
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        <h2 class="mb-3 text-sm font-semibold text-stone-900">Avaliações</h2>
        @forelse ($reviews as $review)
            <div class="mb-2 rounded-xl border border-stone-200 bg-white p-4 text-sm shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-stone-900">{{ $review->reviewer->name }}</span>
                    <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                </div>
                @if ($review->comment)
                    <p class="mt-1 text-stone-600">{{ $review->comment }}</p>
                @endif
            </div>
        @empty
            <x-ui.empty-state title="Ainda sem avaliações" />
        @endforelse
    </div>
</div>
