<x-layouts.app title="Pesquisar ofertas — AgroLink MZ">
    <h1 class="mb-1 text-lg font-semibold">Ofertas disponíveis</h1>
    <p class="mb-6 text-sm text-stone-500">Corredor Dondo/Nhamatanda — Beira.</p>

    <div class="mb-6 grid grid-cols-2 gap-3 rounded border border-stone-200 p-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="col-span-2 sm:col-span-1">
            <label class="block text-xs font-medium text-stone-500" for="category_id">Categoria</label>
            <select wire:model.live="category_id" id="category_id" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                <option value="">Todas</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-stone-500" for="min_price">Preço mín.</label>
            <input wire:model.live.debounce.500ms="min_price" id="min_price" type="text" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
        </div>

        <div>
            <label class="block text-xs font-medium text-stone-500" for="max_price">Preço máx.</label>
            <input wire:model.live.debounce.500ms="max_price" id="max_price" type="text" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
        </div>

        <div>
            <label class="block text-xs font-medium text-stone-500" for="min_quantity">Qtd. mín.</label>
            <input wire:model.live.debounce.500ms="min_quantity" id="min_quantity" type="text" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
        </div>

        <div>
            <label class="block text-xs font-medium text-stone-500" for="location_id">Perto de</label>
            <select wire:model.live="location_id" id="location_id" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                <option value="">Qualquer local</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </select>
        </div>

        @if ($location_id)
            <div>
                <label class="block text-xs font-medium text-stone-500" for="radius_km">Raio (km)</label>
                <input wire:model.live.debounce.500ms="radius_km" id="radius_km" type="number" min="1" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @forelse ($listings as $listing)
            <a href="{{ route('ofertas.show', $listing) }}" wire:navigate class="block rounded border border-stone-200 p-4 hover:border-green-600">
                <p class="font-medium">{{ $listing->product->name }}</p>
                <p class="text-sm text-stone-500">{{ $listing->product->category->name }}</p>
                <p class="mt-2 text-sm">
                    {{ $listing->quantity }} {{ $listing->unit }} disponível ·
                    <span class="font-medium">{{ number_format((float) $listing->price, 2) }} MZN</span>/{{ $listing->unit }}
                </p>
                <p class="mt-1 text-xs text-stone-500">
                    {{ $listing->producer->user->name }}
                    @if ($listing->farm) · {{ $listing->farm->district ?? $listing->farm->name }} @endif
                    @isset($listing->distance_km) · {{ number_format($listing->distance_km, 1) }} km @endisset
                </p>
            </a>
        @empty
            <p class="text-sm text-stone-500">Nenhuma oferta encontrada com estes filtros.</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $listings->links() }}
    </div>
</x-layouts.app>