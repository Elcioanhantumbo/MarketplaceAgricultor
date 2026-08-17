<div>
    @php
        $statusColors = ['disponivel' => 'green', 'reservado' => 'amber', 'vendido' => 'stone', 'expirado' => 'red'];
        $statusLabels = ['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'expirado' => 'Expirado'];
    @endphp

    <x-ui.page-header title="Minhas ofertas" subtitle="Publique quantidade, preço e período de disponibilidade de cada produto." />

    <x-ui.card class="mb-8 max-w-lg" :title="$editingId ? 'Editar oferta' : 'Nova oferta'">
        <form wire:submit="save" class="space-y-4">
            <x-ui.field name="product_id" label="Produto" required>
                <x-ui.select name="product_id" wire:model.live="product_id">
                    <option value="">— Seleccionar —</option>
                    @foreach ($this->categories as $category)
                        <optgroup label="{{ $category->name }}">
                            @foreach ($category->products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <x-ui.field name="farm_id" label="Propriedade" hint="Opcional — define a localização da oferta.">
                <x-ui.select name="farm_id" wire:model="farm_id">
                    <option value="">— Sem propriedade associada —</option>
                    @foreach ($this->farms as $farm)
                        <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field name="quantity" label="Quantidade" required>
                    <x-ui.input name="quantity" wire:model="quantity" type="text" />
                </x-ui.field>
                <x-ui.field name="unit" label="Unidade" required>
                    <x-ui.input name="unit" wire:model="unit" type="text" placeholder="kg" />
                </x-ui.field>
            </div>

            <x-ui.field name="price" label="Preço por unidade (MZN)" required>
                <x-ui.input name="price" wire:model="price" type="text" />
            </x-ui.field>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field name="available_from" label="Disponível desde" required>
                    <x-ui.input name="available_from" wire:model="available_from" type="date" />
                </x-ui.field>
                <x-ui.field name="available_until" label="Disponível até" required>
                    <x-ui.input name="available_until" wire:model="available_until" type="date" />
                </x-ui.field>
            </div>

            <div class="flex gap-2">
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    {{ $editingId ? 'Guardar alterações' : 'Publicar oferta' }}
                </x-ui.button>
                @if ($editingId)
                    <x-ui.button type="button" variant="ghost" wire:click="cancel">Cancelar</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <div class="space-y-3">
        @forelse ($this->listings as $listing)
            <div class="flex items-center justify-between rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="font-medium text-stone-900">{{ $listing->product->name }}</p>
                    <p class="text-sm text-stone-500">
                        {{ $listing->quantity }} {{ $listing->unit }} · {{ number_format((float) $listing->price, 2) }} MZN
                        · {{ $listing->available_from->format('d/m') }}–{{ $listing->available_until->format('d/m/Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <x-ui.badge :color="$statusColors[$listing->status] ?? 'stone'">{{ $statusLabels[$listing->status] ?? ucfirst($listing->status) }}</x-ui.badge>
                    @if ($listing->status === 'disponivel')
                        <button wire:click="edit({{ $listing->id }})" class="font-medium text-green-700 hover:underline">Editar</button>
                        <button wire:click="close({{ $listing->id }})" wire:confirm="Encerrar esta oferta?" class="font-medium text-red-600 hover:underline">Encerrar</button>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Ainda não tem ofertas publicadas" />
        @endforelse
    </div>
</div>
