<div>
    <h1 class="mb-1 text-lg font-semibold">Painel administrativo</h1>
    <x-admin-nav />

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
        <div class="rounded border border-stone-200 p-4">
            <p class="text-xs text-stone-500">Ofertas publicadas</p>
            <p class="mt-1 text-2xl font-semibold">{{ $kpis['ofertas_publicadas'] }}</p>
        </div>
        <div class="rounded border border-stone-200 p-4">
            <p class="text-xs text-stone-500">Taxa de conclusão</p>
            <p class="mt-1 text-2xl font-semibold">{{ $kpis['taxa_conclusao'] !== null ? $kpis['taxa_conclusao'].'%' : '—' }}</p>
        </div>
        <div class="rounded border border-stone-200 p-4">
            <p class="text-xs text-stone-500">Entregas confirmadas</p>
            <p class="mt-1 text-2xl font-semibold">{{ $kpis['taxa_entrega_sucesso'] !== null ? $kpis['taxa_entrega_sucesso'].'%' : '—' }}</p>
        </div>
        <div class="rounded border border-stone-200 p-4">
            <p class="text-xs text-stone-500">Valor transaccionado (GMV)</p>
            <p class="mt-1 text-2xl font-semibold">{{ number_format((float) $kpis['gmv'], 2) }} MZN</p>
        </div>
        <div class="rounded border border-stone-200 p-4">
            <p class="text-xs text-stone-500">Recompra</p>
            <p class="mt-1 text-2xl font-semibold">{{ $kpis['taxa_recompra'] !== null ? $kpis['taxa_recompra'].'%' : '—' }}</p>
        </div>
    </div>
</div>
