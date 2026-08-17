<div>
    <x-ui.page-header title="Categorias e produtos" />
    <x-admin-nav />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.card title="Nova categoria">
            <form wire:submit="addCategory" class="space-y-3">
                <x-ui.field name="category_name">
                    <x-ui.input name="category_name" wire:model="category_name" type="text" placeholder="Nome" class="text-sm" />
                </x-ui.field>
                <x-ui.button type="submit" size="sm">Adicionar categoria</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card title="Novo produto de referência">
            <form wire:submit="addProduct" class="space-y-3">
                <x-ui.field name="product_category_id">
                    <x-ui.select name="product_category_id" wire:model="product_category_id" class="text-sm">
                        <option value="">— Categoria —</option>
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field name="product_name">
                    <x-ui.input name="product_name" wire:model="product_name" type="text" placeholder="Nome do produto" class="text-sm" />
                </x-ui.field>
                <x-ui.field name="product_unit">
                    <x-ui.input name="product_unit" wire:model="product_unit" type="text" placeholder="Unidade (ex.: kg)" class="text-sm" />
                </x-ui.field>
                <x-ui.button type="submit" size="sm">Adicionar produto</x-ui.button>
            </form>
        </x-ui.card>
    </div>

    <div class="mt-8 space-y-6">
        @foreach ($this->categories as $category)
            <div>
                <h2 class="mb-2 flex items-center gap-2 text-sm font-semibold text-stone-900">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span>
                    {{ $category->name }}
                </h2>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($category->products as $product)
                        <div class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700">{{ $product->name }} <span class="text-stone-400">({{ $product->default_unit }})</span></div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
