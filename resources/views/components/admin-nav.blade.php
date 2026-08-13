@php
    $links = [
        'admin.dashboard' => ['route' => 'admin.dashboard', 'label' => 'KPIs'],
        'admin.utilizadores' => ['route' => 'admin.utilizadores', 'label' => 'Utilizadores'],
        'admin.verificacoes' => ['route' => 'admin.verificacoes', 'label' => 'Verificações'],
        'admin.categorias' => ['route' => 'admin.categorias', 'label' => 'Categorias'],
        'admin.ofertas' => ['route' => 'admin.ofertas', 'label' => 'Ofertas'],
        'admin.pedidos' => ['route' => 'admin.pedidos', 'label' => 'Pedidos'],
        'admin.disputas' => ['route' => 'admin.disputas', 'label' => 'Disputas'],
        'entregas' => ['route' => 'entregas', 'label' => 'Entregas'],
    ];
@endphp

<nav class="mb-6 flex flex-wrap gap-2 border-b border-stone-200 pb-4 text-sm">
    @foreach ($links as $name => $link)
        <a href="{{ route($link['route']) }}" wire:navigate
           class="rounded px-3 py-1.5 {{ request()->routeIs($link['route']) ? 'bg-green-700 text-white' : 'text-stone-600 hover:bg-stone-100' }}">
            {{ $link['label'] }}
        </a>
    @endforeach
</nav>