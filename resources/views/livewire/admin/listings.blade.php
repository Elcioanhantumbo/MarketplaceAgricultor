<x-layouts.app title="Ofertas — AgroLink MZ">
    <h1 class="mb-1 text-lg font-semibold">Ofertas</h1>
    <x-admin-nav />

    <select wire:model.live="status" class="mb-4 rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
        <option value="">Todos os estados</option>
        <option value="disponivel">Disponível</option>
        <option value="reservado">Reservado</option>
        <option value="vendido">Vendido</option>
        <option value="expirado">Expirado</option>
    </select>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-200 text-left text-xs text-stone-500">
                    <th class="py-2">Produto</th>
                    <th class="py-2">Produtor</th>
                    <th class="py-2">Quantidade</th>
                    <th class="py-2">Preço</th>
                    <th class="py-2">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($listings as $listing)
                    <tr class="border-b border-stone-100">
                        <td class="py-2">{{ $listing->product->name }}</td>
                        <td class="py-2">{{ $listing->producer->user->name }}</td>
                        <td class="py-2">{{ $listing->quantity }} {{ $listing->unit }}</td>
                        <td class="py-2">{{ number_format((float) $listing->price, 2) }} MZN</td>
                        <td class="py-2">{{ ucfirst($listing->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $listings->links() }}</div>
</x-layouts.app>