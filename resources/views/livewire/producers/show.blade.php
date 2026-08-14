<div>
    <div class="rounded border border-stone-200 p-6">
        <div class="flex items-center gap-4">
            @if ($producer->user->profile?->avatar_path)
                <img src="{{ asset('storage/'.$producer->user->profile->avatar_path) }}" class="h-16 w-16 rounded-full object-cover" alt="">
            @else
                <div class="h-16 w-16 rounded-full bg-stone-100"></div>
            @endif
            <div>
                <h1 class="text-xl font-semibold">{{ $producer->business_name ?: $producer->user->name }}</h1>
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
            <p class="mt-4 text-sm text-stone-600">{{ $bio }}</p>
        @endif

        @if ($district = $producer->user->profile?->district)
            <p class="mt-2 text-sm text-stone-500">📍 {{ collect([$district, $producer->user->profile?->province])->filter()->implode(', ') }}</p>
        @endif
    </div>

    <div class="mt-6">
        <h2 class="mb-2 text-sm font-medium text-stone-500">Ofertas disponíveis</h2>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @forelse ($listings as $listing)
                <a href="{{ route('ofertas.show', $listing) }}" wire:navigate class="rounded border border-stone-200 p-4 text-sm hover:border-green-600">
                    <p class="font-medium">{{ $listing->product->name }}</p>
                    <p class="text-stone-500">{{ $listing->quantity }} {{ $listing->unit }} · {{ number_format((float) $listing->price, 2) }} MZN</p>
                </a>
            @empty
                <p class="text-sm text-stone-500">Sem ofertas disponíveis de momento.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        <h2 class="mb-2 text-sm font-medium text-stone-500">Avaliações</h2>
        @forelse ($reviews as $review)
            <div class="mb-2 rounded border border-stone-200 p-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="font-medium">{{ $review->reviewer->name }}</span>
                    <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                </div>
                @if ($review->comment)
                    <p class="mt-1 text-stone-600">{{ $review->comment }}</p>
                @endif
            </div>
        @empty
            <p class="text-sm text-stone-500">Ainda sem avaliações.</p>
        @endforelse
    </div>
</div>
