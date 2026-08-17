<div>
    <x-ui.page-header title="Meus pedidos" />

    <div class="space-y-3">
        @forelse ($orders as $order)
            <a href="{{ route('pedidos.show', $order) }}" wire:navigate class="block rounded-xl border border-stone-200 bg-white p-4 shadow-sm transition hover:border-green-600 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="font-medium text-stone-900">{{ $order->items->first()?->productListing?->product?->name }}</p>
                    <x-order-status-badge :status="$order->status" />
                </div>
                <p class="mt-1 text-sm text-stone-500">
                    Pedido #{{ $order->id }} · {{ number_format((float) $order->total_amount, 2) }} MZN · {{ $order->created_at->format('d/m/Y') }}
                </p>
            </a>
        @empty
            <x-ui.empty-state title="Ainda não fez nenhum pedido">
                <a href="{{ route('ofertas') }}" wire:navigate class="font-medium text-green-700 hover:underline">Pesquisar ofertas</a>
            </x-ui.empty-state>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
