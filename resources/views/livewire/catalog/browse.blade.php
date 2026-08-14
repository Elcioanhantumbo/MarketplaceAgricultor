<div>
    <h1 class="mb-1 text-lg font-semibold">Categorias e produtos</h1>
    <p class="mb-6 text-sm text-stone-500">Catálogo de referência do piloto Dondo/Nhamatanda — Beira.</p>

    <div class="space-y-6">
        @foreach ($categories as $category)
            <div>
                <h2 class="mb-2 font-medium text-green-700">{{ $category->name }}</h2>
                <ul class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($category->products as $product)
                        <li class="rounded border border-stone-200 px-3 py-2 text-sm">
                            {{ $product->name }}
                            <span class="text-stone-400">({{ $product->default_unit }})</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>
