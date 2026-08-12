<x-layouts.app title="Minhas ofertas — AgroLink MZ">
    <h1 class="mb-1 text-lg font-semibold">Minhas ofertas</h1>
    <p class="mb-6 text-sm text-stone-500">Publique quantidade, preço e período de disponibilidade de cada produto.</p>

    <form wire:submit="save" class="mb-8 max-w-md space-y-4 rounded border border-stone-200 p-4">
        <p class="text-sm font-medium">{{ $editingId ? 'Editar oferta' : 'Nova oferta' }}</p>

        <div>
            <label class="block text-sm font-medium" for="product_id">Produto</label>
            <select wire:model.live="product_id" id="product_id" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                <option value="">— Seleccionar —</option>
                @foreach ($this->categories as $category)
                    <optgroup label="{{ $category->name }}">
                        @foreach ($category->products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('product_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="farm_id">Propriedade (opcional, define a localização)</label>
            <select wire:model="farm_id" id="farm_id" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                <option value="">— Sem propriedade associada —</option>
                @foreach ($this->farms as $farm)
                    <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium" for="quantity">Quantidade</label>
                <input wire:model="quantity" id="quantity" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="unit">Unidade</label>
                <input wire:model="unit" id="unit" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                @error('unit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium" for="price">Preço por unidade (MZN)</label>
            <input wire:model="price" id="price" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium" for="available_from">Disponível desde</label>
                <input wire:model="available_from" id="available_from" type="date" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                @error('available_from') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="available_until">Disponível até</label>
                <input wire:model="available_until" id="available_until" type="date" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                @error('available_until') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800" wire:loading.attr="disabled">
                {{ $editingId ? 'Guardar alterações' : 'Publicar oferta' }}
            </button>
            @if ($editingId)
                <button type="button" wire:click="cancel" class="rounded px-4 py-2 text-sm text-stone-600 hover:text-stone-900">Cancelar</button>
            @endif
        </div>
    </form>

    <div class="space-y-3">
        @forelse ($this->listings as $listing)
            <div class="flex items-center justify-between rounded border border-stone-200 p-4">
                <div>
                    <p class="font-medium">{{ $listing->product->name }}</p>
                    <p class="text-sm text-stone-500">
                        {{ $listing->quantity }} {{ $listing->unit }} · {{ number_format((float) $listing->price, 2) }} MZN
                        · {{ $listing->available_from->format('d/m') }}–{{ $listing->available_until->format('d/m/Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium
                        @class([
                            'bg-green-100 text-green-800' => $listing->status === 'disponivel',
                            'bg-amber-100 text-amber-800' => $listing->status === 'reservado',
                            'bg-stone-200 text-stone-600' => $listing->status === 'vendido',
                            'bg-red-100 text-red-700' => $listing->status === 'expirado',
                        ])">
                        {{ ucfirst($listing->status) }}
                    </span>
                    @if ($listing->status === 'disponivel')
                        <button wire:click="edit({{ $listing->id }})" class="text-green-700 hover:underline">Editar</button>
                        <button wire:click="close({{ $listing->id }})" wire:confirm="Encerrar esta oferta?" class="text-red-600 hover:underline">Encerrar</button>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-stone-500">Ainda não tem ofertas publicadas.</p>
        @endforelse
    </div>
</x-layouts.app>