<div>
    <x-ui.page-header title="Ofertas disponíveis" subtitle="Corredor Dondo/Nhamatanda — Beira." />

    <x-ui.card class="mb-6 p-4!">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="col-span-2 sm:col-span-1">
                <label class="mb-1 block text-xs font-medium text-stone-500" for="category_id">Categoria</label>
                <x-ui.select name="category_id" wire:model.live="category_id" class="text-sm">
                    <option value="">Todas</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-stone-500" for="min_price">Preço mín.</label>
                <x-ui.input name="min_price" wire:model.live.debounce.500ms="min_price" type="text" class="text-sm" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-stone-500" for="max_price">Preço máx.</label>
                <x-ui.input name="max_price" wire:model.live.debounce.500ms="max_price" type="text" class="text-sm" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-stone-500" for="min_quantity">Qtd. mín.</label>
                <x-ui.input name="min_quantity" wire:model.live.debounce.500ms="min_quantity" type="text" class="text-sm" />
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-stone-500" for="location_id">Perto de</label>
                <x-ui.select name="location_id" wire:model.live="location_id" class="text-sm">
                    <option value="">Qualquer local</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            @if ($location_id)
                <div>
                    <label class="mb-1 block text-xs font-medium text-stone-500" for="radius_km">Raio (km)</label>
                    <x-ui.input name="radius_km" wire:model.live.debounce.500ms="radius_km" type="number" min="1" class="text-sm" />
                </div>
            @endif
        </div>
    </x-ui.card>

    @php
        $mapMarkers = $listings->filter(fn ($l) => $l->latitude && $l->longitude)->map(fn ($l) => [
            'lat' => (float) $l->latitude,
            'lng' => (float) $l->longitude,
            'popup' => e($l->product->name).' — '.number_format((float) $l->price, 2).' MZN',
        ])->values();
    @endphp
    @if ($mapMarkers->isNotEmpty())
        <div wire:key="map-{{ $listings->pluck('id')->implode('-') }}"
             x-data="listingsMap({{ $mapMarkers->toJson() }})"
             class="mb-6 h-64 overflow-hidden rounded-xl border border-stone-200 shadow-sm"
             wire:ignore></div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @forelse ($listings as $listing)
            <a href="{{ route('ofertas.show', $listing) }}" wire:navigate class="group block rounded-xl border border-stone-200 bg-white p-4 shadow-sm transition hover:border-green-600 hover:shadow-md">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold text-stone-900 group-hover:text-green-700">{{ $listing->product->name }}</p>
                        <p class="text-sm text-stone-500">{{ $listing->product->category->name }}</p>
                    </div>
                    <span class="shrink-0 text-right">
                        <span class="block text-sm font-semibold text-stone-900">{{ number_format((float) $listing->price, 2) }} MZN</span>
                        <span class="text-xs text-stone-400">/{{ $listing->unit }}</span>
                    </span>
                </div>
                <p class="mt-3 text-sm text-stone-600">{{ $listing->quantity }} {{ $listing->unit }} disponível</p>
                <p class="mt-2 flex items-center gap-1 text-xs text-stone-500">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                    {{ $listing->producer->user->name }}
                    @if ($listing->farm) · {{ $listing->farm->district ?? $listing->farm->name }} @endif
                    @isset($listing->distance_km) · {{ number_format($listing->distance_km, 1) }} km @endisset
                </p>
            </a>
        @empty
            <div class="sm:col-span-2">
                <x-ui.empty-state title="Nenhuma oferta encontrada" icon="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z">
                    Tente alargar os filtros de pesquisa.
                </x-ui.empty-state>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $listings->links() }}
    </div>
</div>
