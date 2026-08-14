<div>
    <h1 class="mb-1 text-lg font-semibold">Ofertas</h1>
    <x-admin-nav />

    <select wire:model.live="status" class="mb-4 rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
        <option value="">Todos os estados</option>
        <option value="disponivel">Disponível</option>
        <option value="reservado">Reservado</option>
        <option value="vendido">Vendido</option>
        <option value="expirado">Expirado</option>
    </select>

    <div class="space-y-2">
        @foreach ($listings as $listing)
            <div class="rounded border border-stone-200 p-3 text-sm">
                <div class="flex items-center justify-between">
                    <p class="font-medium">{{ $listing->product->name }}</p>
                    <span class="text-xs text-stone-500">{{ ucfirst($listing->status) }}</span>
                </div>
                <p class="mt-1 text-stone-500">
                    {{ $listing->producer->user->name }} · {{ $listing->quantity }} {{ $listing->unit }}
                    · {{ number_format((float) $listing->price, 2) }} MZN
                </p>
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $listings->links() }}</div>
</div>
