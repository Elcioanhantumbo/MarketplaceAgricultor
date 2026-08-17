<div>
@php
    $labels = [
        'solicitada' => 'Solicitada', 'atribuida' => 'Atribuída', 'em_recolha' => 'Em recolha',
        'em_transito' => 'Em trânsito', 'entregue' => 'Entregue', 'confirmada' => 'Confirmada',
    ];
    $next = ['atribuida' => 'em_recolha', 'em_recolha' => 'em_transito', 'em_transito' => 'entregue'];
    $nextLabels = ['em_recolha' => 'Marcar em recolha', 'em_transito' => 'Marcar em trânsito', 'entregue' => 'Marcar entregue'];
@endphp

    <x-ui.page-header title="Coordenação de entregas" subtitle="Transporte intermediado — piloto com coordenação assistida." />

    <div class="space-y-3">
        @forelse ($this->deliveries as $delivery)
            <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-stone-900">
                            Pedido #{{ $delivery->order_id }} —
                            {{ $delivery->order->items->first()?->productListing?->product?->name }}
                        </p>
                        <p class="text-sm text-stone-500">Comprador: {{ $delivery->order->buyer->name }}</p>
                    </div>
                    <x-ui.badge color="blue">{{ $labels[$delivery->status] }}</x-ui.badge>
                </div>

                @if ($delivery->status === 'solicitada')
                    @if ($assigningId === $delivery->id)
                        <form wire:submit="assign" class="mt-4 grid grid-cols-1 gap-3 border-t border-stone-100 pt-4 sm:grid-cols-2">
                            <x-ui.field name="transporter_id" label="Transportador registado" hint="Opcional.">
                                <x-ui.select name="transporter_id" wire:model="transporter_id" class="text-sm">
                                    <option value="">— Nenhum —</option>
                                    @foreach ($this->transporters as $transporter)
                                        <option value="{{ $transporter->id }}">{{ $transporter->user->name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>
                            <x-ui.field name="transporter_contact" label="Contacto do transportador" hint="Piloto — nome e telefone.">
                                <x-ui.input name="transporter_contact" wire:model="transporter_contact" type="text" placeholder="Nome e telefone" class="text-sm" />
                            </x-ui.field>
                            <x-ui.field name="cost" label="Custo acordado (MZN)">
                                <x-ui.input name="cost" wire:model="cost" type="text" class="text-sm" />
                            </x-ui.field>
                            <x-ui.field name="pickup_at" label="Recolha prevista">
                                <x-ui.input name="pickup_at" wire:model="pickup_at" type="datetime-local" class="text-sm" />
                            </x-ui.field>
                            <div class="col-span-full flex gap-2">
                                <x-ui.button type="submit" size="sm">Atribuir</x-ui.button>
                                <x-ui.button type="button" variant="ghost" size="sm" wire:click="cancelAssigning">Cancelar</x-ui.button>
                            </div>
                        </form>
                    @else
                        <x-ui.button size="sm" class="mt-3" wire:click="startAssigning({{ $delivery->id }})">Atribuir transportador</x-ui.button>
                    @endif
                @elseif (isset($next[$delivery->status]))
                    <x-ui.button size="sm" class="mt-3" wire:click="advance({{ $delivery->id }}, '{{ $next[$delivery->status] }}')">
                        {{ $nextLabels[$next[$delivery->status]] }}
                    </x-ui.button>
                @endif
            </div>
        @empty
            <x-ui.empty-state title="Sem entregas por coordenar" />
        @endforelse
    </div>
</div>
