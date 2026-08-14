<div>
    <h1 class="mb-1 text-lg font-semibold">Pedidos</h1>
    <x-admin-nav />

    <select wire:model.live="status" class="mb-4 rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
        <option value="">Todos os estados</option>
        <option value="pendente">Pendente</option>
        <option value="aceite">Aceite</option>
        <option value="em_preparacao">Em preparação</option>
        <option value="pronto">Pronto</option>
        <option value="em_transporte">Em transporte</option>
        <option value="entregue">Entregue</option>
        <option value="concluido">Concluído</option>
        <option value="rejeitado">Rejeitado</option>
        <option value="cancelado">Cancelado</option>
    </select>

    <div class="space-y-2">
        @foreach ($orders as $order)
            <a href="{{ route('pedidos.show', $order) }}" wire:navigate class="flex items-center justify-between rounded border border-stone-200 p-3 text-sm hover:border-green-600">
                <span>
                    #{{ $order->id }} — {{ $order->items->first()?->productListing?->product?->name }}
                    · {{ $order->buyer->name }} → {{ $order->producer->user->name }}
                </span>
                <x-order-status-badge :status="$order->status" />
            </a>
        @endforeach
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>
