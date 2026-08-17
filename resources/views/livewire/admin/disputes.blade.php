<div>
@php
    $labels = ['aberta' => 'Aberta', 'em_analise' => 'Em análise', 'procedente' => 'Procedente', 'improcedente' => 'Improcedente', 'resolvida' => 'Resolvida'];
    $colors = ['aberta' => 'amber', 'em_analise' => 'blue', 'procedente' => 'red', 'improcedente' => 'stone', 'resolvida' => 'green'];
@endphp

    <x-ui.page-header title="Disputas" subtitle="Decisão administrativa sobre reclamações reportadas após a entrega." />
    <x-admin-nav />

    <div class="space-y-3">
        @forelse ($this->complaints as $complaint)
            <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-stone-900">
                        Pedido #{{ $complaint->order_id }} — reportado por {{ $complaint->raisedBy->name }}
                    </p>
                    <x-ui.badge :color="$colors[$complaint->status] ?? 'stone'">{{ $labels[$complaint->status] }}</x-ui.badge>
                </div>
                <p class="mt-2 text-sm text-stone-600">{{ $complaint->description }}</p>
                @if ($complaint->resolution)
                    <p class="mt-2 border-t border-stone-100 pt-2 text-sm text-stone-500">Resolução: {{ $complaint->resolution }}</p>
                @endif

                @if (in_array($complaint->status, ['aberta', 'em_analise']))
                    @if ($resolvingId === $complaint->id)
                        <form wire:submit="resolve" class="mt-3 space-y-2 border-t border-stone-100 pt-3">
                            <x-ui.select name="resolution_status" wire:model="resolution_status" class="text-sm">
                                <option value="procedente">Procedente</option>
                                <option value="improcedente">Improcedente</option>
                                <option value="resolvida">Resolvida</option>
                            </x-ui.select>
                            <x-ui.field name="resolution">
                                <x-ui.textarea name="resolution" wire:model="resolution" rows="2" placeholder="Decisão e justificação…" class="text-sm" />
                            </x-ui.field>
                            <div class="flex gap-2">
                                <x-ui.button type="submit" size="sm">Registar decisão</x-ui.button>
                                <x-ui.button type="button" variant="ghost" size="sm" wire:click="cancelResolving">Cancelar</x-ui.button>
                            </div>
                        </form>
                    @else
                        <button wire:click="startResolving({{ $complaint->id }})" class="mt-3 text-sm font-medium text-green-700 hover:underline">Resolver</button>
                    @endif
                @endif
            </div>
        @empty
            <x-ui.empty-state title="Sem disputas reportadas" />
        @endforelse
    </div>
</div>
