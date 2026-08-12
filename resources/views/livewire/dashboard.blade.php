<x-layouts.app title="Painel — AgroLink MZ">
    <h1 class="mb-1 text-lg font-semibold">Bem-vindo, {{ auth()->user()->name }}</h1>
    <p class="text-sm text-stone-500">
        Papel: <span class="font-medium">{{ match (auth()->user()->role) {
            'producer' => 'Produtor',
            'buyer' => 'Comprador',
            'transporter' => 'Transportador',
            'admin' => 'Administrador',
            'operator' => 'Operador',
        } }}</span>
        · Telefone verificado em {{ auth()->user()->phone_verified_at->format('d/m/Y H:i') }}
    </p>

    <div class="mt-6 rounded border border-dashed border-stone-300 p-4 text-sm text-stone-500">
        As próximas fases (perfis, categorias, ofertas, pedidos…) vão preencher este painel.
    </div>
</x-layouts.app>