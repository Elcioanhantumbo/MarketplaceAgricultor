<x-layouts.app title="{{ $listing->product->name }} — AgroLink MZ">
    <a href="{{ route('ofertas') }}" wire:navigate class="text-sm text-green-700 hover:underline">&larr; Voltar às ofertas</a>

    <div class="mt-4 rounded border border-stone-200 p-6">
        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">{{ $listing->product->category->name }}</span>
        <h1 class="mt-2 text-xl font-semibold">{{ $listing->product->name }}</h1>

        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-stone-500">Quantidade disponível</dt>
                <dd class="font-medium">{{ $listing->quantity }} {{ $listing->unit }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Preço</dt>
                <dd class="font-medium">{{ number_format((float) $listing->price, 2) }} MZN / {{ $listing->unit }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Disponível</dt>
                <dd class="font-medium">{{ $listing->available_from->format('d/m/Y') }} – {{ $listing->available_until->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Localização</dt>
                <dd class="font-medium">
                    {{ collect([$listing->farm?->district, $listing->farm?->province])->filter()->implode(', ') ?: 'Não indicada' }}
                </dd>
            </div>
        </dl>
    </div>

    <div class="mt-4 rounded border border-stone-200 p-6">
        <h2 class="text-sm font-medium text-stone-500">Produtor</h2>
        <p class="mt-1 font-medium">{{ $listing->producer->business_name ?: $listing->producer->user->name }}</p>
        @if ($listing->producer->business_name)
            <p class="text-sm text-stone-500">{{ $listing->producer->user->name }}</p>
        @endif
        @if ($bio = $listing->producer->user->profile?->bio)
            <p class="mt-2 text-sm text-stone-600">{{ $bio }}</p>
        @endif
    </div>

    <p class="mt-6 text-sm text-stone-400">O pedido de compra estará disponível numa próxima fase.</p>
</x-layouts.app>