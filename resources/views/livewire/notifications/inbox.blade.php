@php
    $eventLabels = [
        'novo_pedido' => 'Novo pedido',
        'pedido_aceite' => 'Pedido aceite',
        'pedido_rejeitado' => 'Pedido rejeitado',
        'pedido_concluido' => 'Pedido concluído',
        'entrega_atribuida' => 'Entrega atribuída',
        'entrega_a_caminho' => 'Entrega a caminho',
        'pagamento_recebido' => 'Pagamento recebido',
    ];
@endphp

<x-layouts.app title="Notificações — AgroLink MZ">
    <h1 class="mb-1 text-lg font-semibold">Notificações</h1>
    <p class="mb-6 text-sm text-stone-500">
        Enviadas por SMS (secção 21). Enquanto o agregador local não estiver contratado, ficam registadas aqui.
    </p>

    <div class="space-y-2">
        @forelse ($notifications as $notification)
            <div class="rounded border border-stone-200 p-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="font-medium">{{ $eventLabels[$notification->event] ?? $notification->event }}</span>
                    <span class="text-xs text-stone-400">{{ $notification->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p class="mt-1 text-stone-600">{{ $notification->message }}</p>
            </div>
        @empty
            <p class="text-sm text-stone-500">Ainda não tem notificações.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</x-layouts.app>