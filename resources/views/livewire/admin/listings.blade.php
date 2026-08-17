<div>
    @php
        $statusColors = ['disponivel' => 'green', 'reservado' => 'amber', 'vendido' => 'stone', 'expirado' => 'red'];
        $statusLabels = ['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'expirado' => 'Expirado'];
    @endphp

    <x-ui.page-header title="Ofertas" />
    <x-admin-nav />

    <x-ui.select wire:model.live="status" class="mb-4 max-w-xs text-sm">
        <option value="">Todos os estados</option>
        <option value="disponivel">Disponível</option>
        <option value="reservado">Reservado</option>
        <option value="vendido">Vendido</option>
        <option value="expirado">Expirado</option>
    </x-ui.select>

    <div class="space-y-2">
        @forelse ($listings as $listing)
            <div class="rounded-xl border border-stone-200 bg-white p-4 text-sm shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="font-medium text-stone-900">{{ $listing->product->name }}</p>
                    <x-ui.badge :color="$statusColors[$listing->status] ?? 'stone'">{{ $statusLabels[$listing->status] ?? ucfirst($listing->status) }}</x-ui.badge>
                </div>
                <p class="mt-1 text-stone-500">
                    {{ $listing->producer->user->name }} · {{ $listing->quantity }} {{ $listing->unit }}
                    · {{ number_format((float) $listing->price, 2) }} MZN
                </p>
            </div>
        @empty
            <x-ui.empty-state title="Nenhuma oferta encontrada" />
        @endforelse
    </div>

    <div class="mt-4">{{ $listings->links() }}</div>
</div>
