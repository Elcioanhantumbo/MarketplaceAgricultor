<div>
    <x-ui.page-header title="Categorias e produtos" subtitle="Catálogo de referência do piloto Dondo/Nhamatanda — Beira." />

    <div class="space-y-8">
        @foreach ($categories as $category)
            <div>
                <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-stone-900">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span>
                    {{ $category->name }}
                </h2>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($category->products as $product)
                        <div class="rounded-lg border border-stone-200 bg-white px-3 py-2.5 text-sm text-stone-700">
                            {{ $product->name }}
                            <span class="text-stone-400">({{ $product->default_unit }})</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
