<div>
    <x-ui.page-header title="Minhas propriedades" subtitle="Registe onde produz. Vai poder associar ofertas a estas propriedades." />

    <x-ui.card class="mb-8 max-w-lg" :title="$editingId ? 'Editar propriedade' : 'Nova propriedade'">
        <form wire:submit="save" class="space-y-4">
            <x-ui.field name="name" label="Nome" required>
                <x-ui.input name="name" wire:model="name" type="text" placeholder="Ex.: Machamba do Rio" />
            </x-ui.field>

            <x-ui.field name="address" label="Endereço" hint="Opcional.">
                <x-ui.input name="address" wire:model="address" type="text" />
            </x-ui.field>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field name="district" label="Distrito">
                    <x-ui.input name="district" wire:model="district" type="text" />
                </x-ui.field>
                <x-ui.field name="province" label="Província">
                    <x-ui.input name="province" wire:model="province" type="text" />
                </x-ui.field>
            </div>

            <div x-data="{ locating: false, error: null }">
                <div class="grid grid-cols-2 gap-4">
                    <x-ui.field name="latitude" label="Latitude" hint="Opcional.">
                        <x-ui.input name="latitude" wire:model="latitude" type="text" placeholder="-19.6103" />
                    </x-ui.field>
                    <x-ui.field name="longitude" label="Longitude" hint="Opcional.">
                        <x-ui.input name="longitude" wire:model="longitude" type="text" placeholder="34.7425" />
                    </x-ui.field>
                </div>
                <button type="button"
                        @click="
                            if (!navigator.geolocation) { error = 'GPS não suportado neste dispositivo.'; return; }
                            locating = true; error = null;
                            navigator.geolocation.getCurrentPosition(
                                (pos) => {
                                    $wire.set('latitude', pos.coords.latitude.toFixed(7));
                                    $wire.set('longitude', pos.coords.longitude.toFixed(7));
                                    locating = false;
                                },
                                () => { error = 'Não foi possível obter a localização.'; locating = false; },
                            );
                        "
                        class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-green-700 hover:underline disabled:opacity-50" :disabled="locating">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                    <span x-show="!locating">Usar a minha localização actual</span>
                    <span x-show="locating">A obter localização…</span>
                </button>
                <p x-show="error" x-text="error" class="mt-1 text-xs text-red-600"></p>
            </div>

            <div class="flex gap-2">
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    {{ $editingId ? 'Guardar alterações' : 'Adicionar propriedade' }}
                </x-ui.button>
                @if ($editingId)
                    <x-ui.button type="button" variant="ghost" wire:click="cancel">Cancelar</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <div class="space-y-3">
        @forelse ($this->farms as $farm)
            <div class="flex items-center justify-between rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="font-medium text-stone-900">{{ $farm->name }}</p>
                    <p class="text-sm text-stone-500">
                        {{ collect([$farm->address, $farm->district, $farm->province])->filter()->implode(', ') ?: 'Sem endereço registado' }}
                    </p>
                </div>
                <div class="flex gap-3 text-sm">
                    <button wire:click="edit({{ $farm->id }})" class="font-medium text-green-700 hover:underline">Editar</button>
                    <button wire:click="delete({{ $farm->id }})" wire:confirm="Remover esta propriedade?" class="font-medium text-red-600 hover:underline">Remover</button>
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Ainda não tem propriedades registadas" />
        @endforelse
    </div>
</div>
