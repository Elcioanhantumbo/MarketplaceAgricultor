<div>
    <a href="{{ route('ofertas') }}" wire:navigate class="inline-flex items-center gap-1 text-sm font-medium text-green-700 hover:underline">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Voltar às ofertas
    </a>

    <x-ui.card class="mt-4">
        <x-ui.badge color="stone">{{ $listing->product->category->name }}</x-ui.badge>
        <h1 class="mt-2 text-xl font-semibold text-stone-900">{{ $listing->product->name }}</h1>

        <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-4 border-t border-stone-100 pt-5 text-sm">
            <div>
                <dt class="text-stone-500">Quantidade disponível</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ $listing->quantity }} {{ $listing->unit }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Preço</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ number_format((float) $listing->price, 2) }} MZN / {{ $listing->unit }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Disponível</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ $listing->available_from->format('d/m/Y') }} – {{ $listing->available_until->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Localização</dt>
                <dd class="mt-0.5 font-medium text-stone-900">
                    {{ collect([$listing->farm?->district, $listing->farm?->province])->filter()->implode(', ') ?: 'Não indicada' }}
                </dd>
            </div>
        </dl>
    </x-ui.card>

    @if ($listing->latitude && $listing->longitude)
        <div x-data="singleLocationMap({{ $listing->latitude }}, {{ $listing->longitude }}, '{{ e($listing->product->name) }}')"
             class="mt-4 h-56 overflow-hidden rounded-xl border border-stone-200 shadow-sm" wire:ignore></div>
    @endif

    <x-ui.card class="mt-4" title="Produtor">
        <a href="{{ route('produtores.show', $listing->producer) }}" wire:navigate class="block font-medium text-green-700 hover:underline">
            {{ $listing->producer->business_name ?: $listing->producer->user->name }}
        </a>
        @if ($listing->producer->business_name)
            <p class="text-sm text-stone-500">{{ $listing->producer->user->name }}</p>
        @endif
        @php $rating = $listing->producer->averageRating(); @endphp
        <p class="mt-1 text-sm">
            @if ($rating)
                <span class="text-amber-500">{{ str_repeat('★', round($rating)) }}{{ str_repeat('☆', 5 - round($rating)) }}</span>
                <span class="text-stone-500">{{ number_format($rating, 1) }}</span>
            @else
                <span class="text-stone-400">Ainda sem avaliações</span>
            @endif
        </p>
        @if ($bio = $listing->producer->user->profile?->bio)
            <p class="mt-2 text-sm text-stone-600">{{ $bio }}</p>
        @endif
    </x-ui.card>

    @auth
        @if (auth()->user()->role === 'buyer')
            <x-ui.card class="mt-4 max-w-sm" title="Fazer pedido">
                <form wire:submit="order" class="space-y-4">
                    <x-ui.field name="quantity" label="Quantidade ({{ $listing->unit }})" required>
                        <x-ui.input name="quantity" wire:model="quantity" type="text" />
                    </x-ui.field>

                    <x-ui.field name="delivery_method" label="Entrega" required>
                        <x-ui.select name="delivery_method" wire:model="delivery_method">
                            <option value="comprador_levanta">Eu levanto na propriedade</option>
                            <option value="produtor_entrega">O produtor entrega</option>
                            <option value="transporte_intermediado">Transporte pela plataforma</option>
                        </x-ui.select>
                    </x-ui.field>

                    <p class="text-sm text-stone-500">
                        Total estimado: <span class="font-semibold text-stone-900">{{ number_format(((float) $quantity ?: 0) * (float) $listing->price, 2) }} MZN</span>
                    </p>

                    <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
                        Enviar pedido
                    </x-ui.button>
                </form>
            </x-ui.card>
        @endif
    @else
        <p class="mt-6 text-sm text-stone-500">
            <a href="{{ route('login') }}" wire:navigate class="font-medium text-green-700 hover:underline">Inicie sessão</a>
            como comprador para fazer um pedido.
        </p>
    @endauth
</div>
