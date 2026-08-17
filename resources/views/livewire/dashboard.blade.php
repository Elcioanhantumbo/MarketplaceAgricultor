<div>
    @php
        $user = auth()->user();
        $roleLabels = [
            'producer' => 'Produtor', 'buyer' => 'Comprador', 'transporter' => 'Transportador',
            'admin' => 'Administrador', 'operator' => 'Operador',
        ];

        $actions = [
            ['route' => 'perfil', 'label' => 'Editar perfil', 'text' => 'Dados pessoais e de contacto', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
        ];
        if ($user->role === 'producer') {
            $actions[] = ['route' => 'minhas-propriedades', 'label' => 'Minhas propriedades', 'text' => 'Onde produz', 'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21'];
            $actions[] = ['route' => 'minhas-ofertas', 'label' => 'Minhas ofertas', 'text' => 'Publicar e gerir produtos', 'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375C2.754 3.75 2.25 4.254 2.25 4.875v1.5c0 .621.504 1.125 1.125 1.125Z'];
            $actions[] = ['route' => 'pedidos-recebidos', 'label' => 'Pedidos recebidos', 'text' => 'Aceitar e acompanhar', 'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'];
        }
        if ($user->role === 'buyer') {
            $actions[] = ['route' => 'meus-pedidos', 'label' => 'Meus pedidos', 'text' => 'Acompanhar encomendas', 'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'];
        }
        if (in_array($user->role, ['operator', 'admin'])) {
            $actions[] = ['route' => 'admin.dashboard', 'label' => 'Painel administrativo', 'text' => 'KPIs, utilizadores, disputas', 'icon' => 'M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25'];
        }
        $actions[] = ['route' => 'ofertas', 'label' => 'Pesquisar ofertas', 'text' => 'Ver produtos disponíveis', 'icon' => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z'];
        $actions[] = ['route' => 'categorias', 'label' => 'Categorias e produtos', 'text' => 'Catálogo de referência', 'icon' => 'M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122'];
    @endphp

    <div class="mb-6 flex items-center gap-4">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-green-100 text-xl font-semibold text-green-700">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <h1 class="text-xl font-semibold text-stone-900">Olá, {{ $user->name }}</h1>
            <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-stone-500">
                <x-ui.badge color="green">{{ $roleLabels[$user->role] }}</x-ui.badge>
                Telefone verificado em {{ $user->phone_verified_at->format('d/m/Y') }}
            </p>
        </div>
    </div>

    @if ($user->role === 'producer' && ! $user->producer->isReadyToPublish())
        <x-ui.alert type="warning" class="mb-6">
            Complete o <a href="{{ route('perfil') }}" class="font-medium underline" wire:navigate>seu perfil</a> e registe
            pelo menos <a href="{{ route('minhas-propriedades') }}" class="font-medium underline" wire:navigate>uma propriedade</a>
            antes de publicar ofertas.
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($actions as $action)
            <a href="{{ route($action['route']) }}" wire:navigate
               class="group flex items-start gap-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm transition hover:border-green-600 hover:shadow-md">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-stone-500 transition group-hover:bg-green-100 group-hover:text-green-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $action['icon'] }}" /></svg>
                </div>
                <div>
                    <p class="font-medium text-stone-900 group-hover:text-green-700">{{ $action['label'] }}</p>
                    <p class="text-sm text-stone-500">{{ $action['text'] }}</p>
                </div>
            </a>
        @endforeach
    </div>
</div>
