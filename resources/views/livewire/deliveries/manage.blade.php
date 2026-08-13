@php
    $labels = [
        'solicitada' => 'Solicitada', 'atribuida' => 'Atribuída', 'em_recolha' => 'Em recolha',
        'em_transito' => 'Em trânsito', 'entregue' => 'Entregue', 'confirmada' => 'Confirmada',
    ];
    $next = ['atribuida' => 'em_recolha', 'em_recolha' => 'em_transito', 'em_transito' => 'entregue'];
    $nextLabels = ['em_recolha' => 'Marcar em recolha', 'em_transito' => 'Marcar em trânsito', 'entregue' => 'Marcar entregue'];
@endphp

<x-layouts.app title="Entregas — AgroLink MZ">
    <h1 class="mb-1 text-lg font-semibold">Coordenação de entregas</h1>
    <p class="mb-6 text-sm text-stone-500">Transporte intermediado — piloto com coordenação assistida (secção 16.2).</p>

    <div class="space-y-3">
        @forelse ($this->deliveries as $delivery)
            <div class="rounded border border-stone-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">
                            Pedido #{{ $delivery->order_id }} —
                            {{ $delivery->order->items->first()?->productListing?->product?->name }}
                        </p>
                        <p class="text-sm text-stone-500">Comprador: {{ $delivery->order->buyer->name }}</p>
                    </div>
                    <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs font-medium text-stone-700">
                        {{ $labels[$delivery->status] }}
                    </span>
                </div>

                @if ($delivery->status === 'solicitada')
                    @if ($assigningId === $delivery->id)
                        <form wire:submit="assign" class="mt-4 grid grid-cols-1 gap-3 border-t border-stone-100 pt-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-stone-500">Transportador registado (opcional)</label>
                                <select wire:model="transporter_id" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                                    <option value="">— Nenhum —</option>
                                    @foreach ($this->transporters as $transporter)
                                        <option value="{{ $transporter->id }}">{{ $transporter->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-500">Contacto do transportador (piloto)</label>
                                <input wire:model="transporter_contact" type="text" placeholder="Nome e telefone" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-500">Custo acordado (MZN)</label>
                                <input wire:model="cost" type="text" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                                @error('cost') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-500">Recolha prevista</label>
                                <input wire:model="pickup_at" type="datetime-local" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                            </div>
                            <div class="col-span-full flex gap-2">
                                <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Atribuir</button>
                                <button type="button" wire:click="cancelAssigning" class="rounded px-4 py-2 text-sm text-stone-600 hover:text-stone-900">Cancelar</button>
                            </div>
                        </form>
                    @else
                        <button wire:click="startAssigning({{ $delivery->id }})" class="mt-3 rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Atribuir transportador</button>
                    @endif
                @elseif (isset($next[$delivery->status]))
                    <button wire:click="advance({{ $delivery->id }}, '{{ $next[$delivery->status] }}')" class="mt-3 rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">
                        {{ $nextLabels[$next[$delivery->status]] }}
                    </button>
                @endif
            </div>
        @empty
            <p class="text-sm text-stone-500">Sem entregas por coordenar.</p>
        @endforelse
    </div>
</x-layouts.app>