<div>
    <h1 class="mb-1 text-lg font-semibold">Categorias e produtos</h1>
    <x-admin-nav />

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <form wire:submit="addCategory" class="space-y-3 rounded border border-stone-200 p-4">
            <p class="text-sm font-medium">Nova categoria</p>
            <input wire:model="category_name" type="text" placeholder="Nome" class="w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
            @error('category_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Adicionar categoria</button>
        </form>

        <form wire:submit="addProduct" class="space-y-3 rounded border border-stone-200 p-4">
            <p class="text-sm font-medium">Novo produto de referência</p>
            <select wire:model="product_category_id" class="w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                <option value="">— Categoria —</option>
                @foreach ($this->categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('product_category_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            <input wire:model="product_name" type="text" placeholder="Nome do produto" class="w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
            @error('product_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            <input wire:model="product_unit" type="text" placeholder="Unidade (ex.: kg)" class="w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
            @error('product_unit') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Adicionar produto</button>
        </form>
    </div>

    <div class="mt-6 space-y-4">
        @foreach ($this->categories as $category)
            <div>
                <h2 class="font-medium text-green-700">{{ $category->name }}</h2>
                <ul class="mt-1 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($category->products as $product)
                        <li class="rounded border border-stone-200 px-3 py-2 text-sm">{{ $product->name }} <span class="text-stone-400">({{ $product->default_unit }})</span></li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>
