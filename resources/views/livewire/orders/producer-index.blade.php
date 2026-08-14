<div>
    <h1 class="mb-6 text-lg font-semibold">Pedidos recebidos</h1>

    <div class="space-y-3">
        @forelse ($orders as $order)
            <a href="{{ route('pedidos.show', $order) }}" wire:navigate class="block rounded border border-stone-200 p-4 hover:border-green-600">
                <div class="flex items-center justify-between">
                    <p class="font-medium">{{ $order->items->first()?->productListing?->product?->name }}</p>
                    <x-order-status-badge :status="$order->status" />
                </div>
                <p class="mt-1 text-sm text-stone-500">
                    Pedido #{{ $order->id }} · {{ $order->buyer->name }} · {{ number_format((float) $order->total_amount, 2) }} MZN · {{ $order->created_at->format('d/m/Y') }}
                </p>
            </a>
        @empty
            <p class="text-sm text-stone-500">Ainda não recebeu pedidos.</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
