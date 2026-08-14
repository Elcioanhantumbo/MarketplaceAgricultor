<div>
@php
    $labels = ['aberta' => 'Aberta', 'em_analise' => 'Em análise', 'procedente' => 'Procedente', 'improcedente' => 'Improcedente', 'resolvida' => 'Resolvida'];
@endphp

    <h1 class="mb-1 text-lg font-semibold">Disputas</h1>
    <x-admin-nav />
    <p class="mb-4 text-sm text-stone-500">RN12/RN27 — decisão administrativa sobre reclamações reportadas após a entrega.</p>

    <div class="space-y-3">
        @forelse ($this->complaints as $complaint)
            <div class="rounded border border-stone-200 p-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium">
                        Pedido #{{ $complaint->order_id }} — reportado por {{ $complaint->raisedBy->name }}
                    </p>
                    <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs font-medium text-stone-700">{{ $labels[$complaint->status] }}</span>
                </div>
                <p class="mt-2 text-sm text-stone-600">{{ $complaint->description }}</p>
                @if ($complaint->resolution)
                    <p class="mt-2 border-t border-stone-100 pt-2 text-sm text-stone-500">Resolução: {{ $complaint->resolution }}</p>
                @endif

                @if (in_array($complaint->status, ['aberta', 'em_analise']))
                    @if ($resolvingId === $complaint->id)
                        <form wire:submit="resolve" class="mt-3 space-y-2 border-t border-stone-100 pt-3">
                            <select wire:model="resolution_status" class="w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                                <option value="procedente">Procedente</option>
                                <option value="improcedente">Improcedente</option>
                                <option value="resolvida">Resolvida</option>
                            </select>
                            <textarea wire:model="resolution" rows="2" placeholder="Decisão e justificação…" class="w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600"></textarea>
                            @error('resolution') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <div class="flex gap-2">
                                <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Registar decisão</button>
                                <button type="button" wire:click="cancelResolving" class="rounded px-4 py-2 text-sm text-stone-600 hover:text-stone-900">Cancelar</button>
                            </div>
                        </form>
                    @else
                        <button wire:click="startResolving({{ $complaint->id }})" class="mt-3 text-sm text-green-700 hover:underline">Resolver</button>
                    @endif
                @endif
            </div>
        @empty
            <p class="text-sm text-stone-500">Sem disputas reportadas.</p>
        @endforelse
    </div>
</div>
