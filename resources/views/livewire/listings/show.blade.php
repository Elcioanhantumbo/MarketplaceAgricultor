<div>
    <a href="{{ route('ofertas') }}" wire:navigate class="text-sm text-green-700 hover:underline">&larr; Voltar às ofertas</a>

    <div class="mt-4 rounded border border-stone-200 p-6">
        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">{{ $listing->product->category->name }}</span>
        <h1 class="mt-2 text-xl font-semibold">{{ $listing->product->name }}</h1>

        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-stone-500">Quantidade disponível</dt>
                <dd class="font-medium">{{ $listing->quantity }} {{ $listing->unit }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Preço</dt>
                <dd class="font-medium">{{ number_format((float) $listing->price, 2) }} MZN / {{ $listing->unit }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Disponível</dt>
                <dd class="font-medium">{{ $listing->available_from->format('d/m/Y') }} – {{ $listing->available_until->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Localização</dt>
                <dd class="font-medium">
                    {{ collect([$listing->farm?->district, $listing->farm?->province])->filter()->implode(', ') ?: 'Não indicada' }}
                </dd>
            </div>
        </dl>
    </div>

    @if ($listing->latitude && $listing->longitude)
        <div x-data="singleLocationMap({{ $listing->latitude }}, {{ $listing->longitude }}, '{{ e($listing->product->name) }}')"
             class="mt-4 h-56 rounded border border-stone-200" wire:ignore></div>
    @endif

    <div class="mt-4 rounded border border-stone-200 p-6">
        <h2 class="text-sm font-medium text-stone-500">Produtor</h2>
        <a href="{{ route('produtores.show', $listing->producer) }}" wire:navigate class="mt-1 block font-medium text-green-700 hover:underline">
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
    </div>

    @auth
        @if (auth()->user()->role === 'buyer')
            <form wire:submit="order" class="mt-4 max-w-sm space-y-4 rounded border border-stone-200 p-6">
                <p class="text-sm font-medium">Fazer pedido</p>

                <div>
                    <label class="block text-sm font-medium" for="quantity">Quantidade ({{ $listing->unit }})</label>
                    <input wire:model="quantity" id="quantity" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                    @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium" for="delivery_method">Entrega</label>
                    <select wire:model="delivery_method" id="delivery_method" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                        <option value="comprador_levanta">Eu levanto na propriedade</option>
                        <option value="produtor_entrega">O produtor entrega</option>
                        <option value="transporte_intermediado">Transporte pela plataforma</option>
                    </select>
                    @error('delivery_method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <p class="text-sm text-stone-500">
                    Total estimado: <span class="font-medium">{{ number_format(((float) $quantity ?: 0) * (float) $listing->price, 2) }} MZN</span>
                </p>

                <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800" wire:loading.attr="disabled">
                    Enviar pedido
                </button>
            </form>
        @endif
    @else
        <p class="mt-6 text-sm text-stone-500">
            <a href="{{ route('login') }}" wire:navigate class="text-green-700 hover:underline">Inicie sessão</a>
            como comprador para fazer um pedido.
        </p>
    @endauth
</div>
