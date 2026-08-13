<x-layouts.app title="AgroLink MZ — Ligar produtores e compradores">
    <div class="text-center">
        <h1 class="text-2xl font-semibold text-stone-900 sm:text-3xl">
            Ligar produtores e compradores no corredor Dondo/Nhamatanda — Beira
        </h1>
        <p class="mx-auto mt-3 max-w-xl text-stone-600">
            Publique ofertas, encontre fornecedores confiáveis, combine entrega e pagamento — tudo num só lugar.
        </p>

        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('ofertas') }}" wire:navigate class="rounded bg-green-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-800">
                Ver ofertas disponíveis
            </a>
            @guest
                <a href="{{ route('registo') }}" wire:navigate class="rounded border border-stone-300 px-5 py-2.5 text-sm font-medium text-stone-700 hover:border-green-600 hover:text-green-700">
                    Criar conta
                </a>
            @endguest
        </div>
    </div>

    <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded border border-stone-200 p-5">
            <h2 class="font-medium text-green-700">Para produtores</h2>
            <p class="mt-2 text-sm text-stone-600">
                Publique quantidade, preço e disponibilidade. Receba pedidos, aceite com reserva automática de stock
                e combine a entrega — por levantamento, entrega própria ou transporte intermediado.
            </p>
        </div>
        <div class="rounded border border-stone-200 p-5">
            <h2 class="font-medium text-green-700">Para compradores</h2>
            <p class="mt-2 text-sm text-stone-600">
                Pesquise por categoria, preço e proximidade. Veja o perfil do produtor, faça o pedido e acompanhe
                cada etapa até à entrega confirmada.
            </p>
        </div>
    </div>
</x-layouts.app>