<x-layouts.app title="AgroLink MZ — Ligar produtores e compradores">
    <div class="-mx-4 -mt-8 mb-10 bg-gradient-to-b from-green-50 to-transparent px-4 pt-14 pb-10 text-center sm:-mx-6 sm:px-6">
        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
            Piloto — corredor Dondo/Nhamatanda — Beira
        </span>
        <h1 class="mx-auto mt-4 max-w-2xl text-3xl font-semibold tracking-tight text-stone-900 sm:text-4xl">
            Ligar produtores agrícolas a compradores profissionais
        </h1>
        <p class="mx-auto mt-4 max-w-xl text-stone-600">
            Publique ofertas, encontre fornecedores confiáveis, combine entrega e pagamento — tudo num só lugar,
            pensado para redes móveis lentas.
        </p>

        <div class="mt-7 flex flex-wrap justify-center gap-3">
            <x-ui.button href="{{ route('ofertas') }}" wire:navigate size="md">
                Ver ofertas disponíveis
            </x-ui.button>
            @guest
                <x-ui.button href="{{ route('registo') }}" wire:navigate variant="secondary" size="md">
                    Criar conta
                </x-ui.button>
            @endguest
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.card>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-green-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
            </div>
            <h2 class="mt-3 font-semibold text-stone-900">Para produtores</h2>
            <p class="mt-1.5 text-sm text-stone-600">
                Publique quantidade, preço e disponibilidade. Receba pedidos, aceite com reserva automática de stock
                e combine a entrega — por levantamento, entrega própria ou transporte intermediado.
            </p>
        </x-ui.card>
        <x-ui.card>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-green-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.8-4.185 2.475-6.75H5.106M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
            </div>
            <h2 class="mt-3 font-semibold text-stone-900">Para compradores</h2>
            <p class="mt-1.5 text-sm text-stone-600">
                Pesquise por categoria, preço e proximidade. Veja o perfil do produtor, faça o pedido e acompanhe
                cada etapa até à entrega confirmada.
            </p>
        </x-ui.card>
    </div>

    <div class="mt-12">
        <h2 class="text-center text-sm font-semibold tracking-wide text-stone-400 uppercase">Como funciona</h2>
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ([
                ['n' => '1', 'title' => 'Publique ou pesquise', 'text' => 'Produtores publicam ofertas; compradores pesquisam por produto, preço e proximidade.'],
                ['n' => '2', 'title' => 'Combine e aceite', 'text' => 'O comprador pede uma quantidade; o produtor aceita e a reserva de stock é automática.'],
                ['n' => '3', 'title' => 'Entregue e avalie', 'text' => 'Acompanhe a entrega e o pagamento até à conclusão, depois avaliem-se mutuamente.'],
            ] as $step)
                <div class="rounded-xl border border-stone-200 bg-white p-5">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-stone-900 text-xs font-semibold text-white">{{ $step['n'] }}</span>
                    <h3 class="mt-3 text-sm font-semibold text-stone-900">{{ $step['title'] }}</h3>
                    <p class="mt-1 text-sm text-stone-500">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
