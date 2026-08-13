<x-layouts.app title="Painel — AgroLink MZ">
    @php $user = auth()->user(); @endphp

    <h1 class="mb-1 text-lg font-semibold">Bem-vindo, {{ $user->name }}</h1>
    <p class="text-sm text-stone-500">
        Papel: <span class="font-medium">{{ match ($user->role) {
            'producer' => 'Produtor',
            'buyer' => 'Comprador',
            'transporter' => 'Transportador',
            'admin' => 'Administrador',
            'operator' => 'Operador',
        } }}</span>
        · Telefone verificado em {{ $user->phone_verified_at->format('d/m/Y H:i') }}
    </p>

    @if ($user->role === 'producer' && ! $user->producer->isReadyToPublish())
        <div class="mt-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Complete o <a href="{{ route('perfil') }}" class="underline" wire:navigate>seu perfil</a> e registe
            pelo menos <a href="{{ route('minhas-propriedades') }}" class="underline" wire:navigate>uma propriedade</a>
            antes de publicar ofertas (RN02).
        </div>
    @endif

    <div class="mt-6 flex flex-wrap gap-3 text-sm">
        <a href="{{ route('perfil') }}" wire:navigate class="rounded border border-stone-200 px-3 py-2 hover:border-green-600 hover:text-green-700">Editar perfil</a>
        @if ($user->role === 'producer')
            <a href="{{ route('minhas-propriedades') }}" wire:navigate class="rounded border border-stone-200 px-3 py-2 hover:border-green-600 hover:text-green-700">Minhas propriedades</a>
            <a href="{{ route('minhas-ofertas') }}" wire:navigate class="rounded border border-stone-200 px-3 py-2 hover:border-green-600 hover:text-green-700">Minhas ofertas</a>
            <a href="{{ route('pedidos-recebidos') }}" wire:navigate class="rounded border border-stone-200 px-3 py-2 hover:border-green-600 hover:text-green-700">Pedidos recebidos</a>
        @endif
        @if ($user->role === 'buyer')
            <a href="{{ route('meus-pedidos') }}" wire:navigate class="rounded border border-stone-200 px-3 py-2 hover:border-green-600 hover:text-green-700">Meus pedidos</a>
        @endif
        @if (in_array($user->role, ['operator', 'admin']))
            <a href="{{ route('entregas') }}" wire:navigate class="rounded border border-stone-200 px-3 py-2 hover:border-green-600 hover:text-green-700">Coordenar entregas</a>
        @endif
        <a href="{{ route('ofertas') }}" wire:navigate class="rounded border border-stone-200 px-3 py-2 hover:border-green-600 hover:text-green-700">Pesquisar ofertas</a>
        <a href="{{ route('categorias') }}" wire:navigate class="rounded border border-stone-200 px-3 py-2 hover:border-green-600 hover:text-green-700">Categorias e produtos</a>
    </div>

    <div class="mt-6 rounded border border-dashed border-stone-300 p-4 text-sm text-stone-500">
        As próximas fases (pagamentos…) vão preencher este painel.
    </div>
</x-layouts.app>