<div>
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

    <x-ui.page-header title="Notificações" subtitle="Enviadas por SMS. Enquanto o agregador local não estiver contratado, ficam registadas aqui." />

    <div class="space-y-2">
        @forelse ($notifications as $notification)
            <div class="rounded-xl border border-stone-200 bg-white p-4 text-sm shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-stone-900">{{ $eventLabels[$notification->event] ?? $notification->event }}</span>
                    <span class="text-xs text-stone-400">{{ $notification->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p class="mt-1 text-stone-600">{{ $notification->message }}</p>
            </div>
        @empty
            <x-ui.empty-state title="Ainda não tem notificações" icon="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
