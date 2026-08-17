<div>
    <x-ui.page-header title="Pedidos" />
    <x-admin-nav />

    <x-ui.select wire:model.live="status" class="mb-4 max-w-xs text-sm">
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
    </x-ui.select>

    <div class="space-y-2">
        @forelse ($orders as $order)
            <a href="{{ route('pedidos.show', $order) }}" wire:navigate class="flex items-center justify-between rounded-xl border border-stone-200 bg-white p-4 text-sm shadow-sm transition hover:border-green-600 hover:shadow-md">
                <span class="text-stone-700">
                    <span class="font-medium text-stone-900">#{{ $order->id }} — {{ $order->items->first()?->productListing?->product?->name }}</span>
                    · {{ $order->buyer->name }} → {{ $order->producer->user->name }}
                </span>
                <x-order-status-badge :status="$order->status" />
            </a>
        @empty
            <x-ui.empty-state title="Nenhum pedido encontrado" />
        @endforelse
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>
