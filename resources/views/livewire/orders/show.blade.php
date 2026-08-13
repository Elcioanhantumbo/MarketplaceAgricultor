@php
    $isProducer = auth()->user()->id === $order->producer->user_id;
    $isBuyer = auth()->user()->id === $order->buyer_id;
    $item = $order->items->first();
@endphp

<x-layouts.app title="Pedido #{{ $order->id }} — AgroLink MZ">
    <a href="{{ $isProducer ? route('pedidos-recebidos') : route('meus-pedidos') }}" wire:navigate class="text-sm text-green-700 hover:underline">&larr; Voltar aos pedidos</a>

    <div class="mt-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold">Pedido #{{ $order->id }}</h1>
        <x-order-status-badge :status="$order->status" class="text-sm" />
    </div>

    @error('action') <p class="mt-2 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p> @enderror

    <div class="mt-4 rounded border border-stone-200 p-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-stone-500">Produto</dt>
                <dd class="font-medium">{{ $item->productListing->product->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Quantidade</dt>
                <dd class="font-medium">{{ $item->quantity }} {{ $item->productListing->unit }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Comprador</dt>
                <dd class="font-medium">{{ $order->buyer->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Produtor</dt>
                <dd class="font-medium">{{ $order->producer->business_name ?: $order->producer->user->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Entrega</dt>
                <dd class="font-medium">{{ match ($order->delivery_method) {
                    'comprador_levanta' => 'Comprador levanta',
                    'produtor_entrega' => 'Produtor entrega',
                    'transporte_intermediado' => 'Transporte pela plataforma',
                } }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Total</dt>
                <dd class="font-medium">{{ number_format((float) $order->total_amount, 2) }} MZN</dd>
            </div>
        </dl>
    </div>

    @if ($order->delivery)
        @php
            $deliveryLabels = [
                'solicitada' => 'Solicitada', 'atribuida' => 'Atribuída', 'em_recolha' => 'Em recolha',
                'em_transito' => 'Em trânsito', 'entregue' => 'Entregue', 'confirmada' => 'Confirmada',
            ];
        @endphp
        <div class="mt-4 rounded border border-stone-200 p-6">
            <h2 class="text-sm font-medium text-stone-500">Entrega (transporte intermediado)</h2>
            <dl class="mt-2 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-stone-500">Estado</dt>
                    <dd class="font-medium">{{ $deliveryLabels[$order->delivery->status] }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Transportador</dt>
                    <dd class="font-medium">{{ $order->delivery->transporter?->user->name ?? $order->delivery->transporter_contact ?? 'Por atribuir' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Custo do transporte</dt>
                    <dd class="font-medium">{{ $order->delivery->cost !== null ? number_format((float) $order->delivery->cost, 2) . ' MZN' : 'Por definir' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Recolha prevista</dt>
                    <dd class="font-medium">{{ $order->delivery->pickup_at?->format('d/m/Y H:i') ?? 'Por agendar' }}</dd>
                </div>
            </dl>
        </div>
    @endif

    <div class="mt-4 flex flex-wrap gap-2">
        @if ($isProducer && $order->status === 'pendente')
            <button wire:click="accept" wire:confirm="Aceitar este pedido? A quantidade será reservada." class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Aceitar</button>
            <button wire:click="reject" wire:confirm="Rejeitar este pedido?" class="rounded border border-stone-300 px-4 py-2 text-sm hover:border-red-500 hover:text-red-600">Rejeitar</button>
        @endif

        @if ($isProducer && $order->status === 'aceite')
            <button wire:click="advance('em_preparacao')" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Iniciar preparação</button>
        @endif
        @if ($isProducer && $order->status === 'em_preparacao')
            <button wire:click="advance('pronto')" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Marcar pronto</button>
        @endif
        @if ($isProducer && $order->status === 'pronto')
            <button wire:click="advance('em_transporte')" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Marcar em transporte</button>
        @endif
        @if ($isProducer && $order->status === 'em_transporte')
            <button wire:click="advance('entregue')" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Marcar entregue</button>
        @endif

        @if ($isBuyer && in_array($order->status, ['pendente', 'aceite']))
            <button wire:click="cancel" wire:confirm="Cancelar este pedido?" class="rounded border border-stone-300 px-4 py-2 text-sm hover:border-red-500 hover:text-red-600">Cancelar pedido</button>
        @endif
        @if ($isBuyer && $order->status === 'entregue' && (! $order->delivery || $order->delivery->status === 'entregue'))
            <button wire:click="confirmDelivery" wire:confirm="Confirmar que recebeu a encomenda?" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Confirmar recepção</button>
        @endif
    </div>

    <div class="mt-8">
        <h2 class="mb-2 text-sm font-medium text-stone-500">Histórico</h2>
        <ul class="space-y-1 text-sm text-stone-600">
            @foreach ($order->statusHistory as $entry)
                <li>
                    {{ $entry->changed_at->format('d/m/Y H:i') }} —
                    {{ $entry->from_status ? ucfirst($entry->from_status) . ' → ' : '' }}{{ ucfirst($entry->to_status) }}
                    ({{ $entry->changedBy?->name ?? 'sistema' }})
                </li>
            @endforeach
        </ul>
    </div>
</x-layouts.app>