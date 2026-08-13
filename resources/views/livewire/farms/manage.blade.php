<x-layouts.app title="Minhas propriedades — AgroLink MZ">
    <h1 class="mb-1 text-lg font-semibold">Minhas propriedades</h1>
    <p class="mb-6 text-sm text-stone-500">Registe onde produz. Vai poder associar ofertas a estas propriedades.</p>

    <form wire:submit="save" class="mb-8 max-w-md space-y-4 rounded border border-stone-200 p-4">
        <p class="text-sm font-medium">{{ $editingId ? 'Editar propriedade' : 'Nova propriedade' }}</p>

        <div>
            <label class="block text-sm font-medium" for="name">Nome</label>
            <input wire:model="name" id="name" type="text" placeholder="Ex.: Machamba do Rio" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="address">Endereço (opcional)</label>
            <input wire:model="address" id="address" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium" for="district">Distrito</label>
                <input wire:model="district" id="district" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            </div>
            <div>
                <label class="block text-sm font-medium" for="province">Província</label>
                <input wire:model="province" id="province" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            </div>
        </div>

        <div x-data="{ locating: false, error: null }">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium" for="latitude">Latitude (opcional)</label>
                    <input wire:model="latitude" id="latitude" type="text" placeholder="-19.6103" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                    @error('latitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium" for="longitude">Longitude (opcional)</label>
                    <input wire:model="longitude" id="longitude" type="text" placeholder="34.7425" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                    @error('longitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
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
                    class="mt-2 text-sm text-green-700 hover:underline disabled:opacity-50" :disabled="locating">
                <span x-show="!locating">📍 Usar a minha localização actual</span>
                <span x-show="locating">A obter localização…</span>
            </button>
            <p x-show="error" x-text="error" class="mt-1 text-xs text-red-600"></p>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800" wire:loading.attr="disabled">
                {{ $editingId ? 'Guardar alterações' : 'Adicionar propriedade' }}
            </button>
            @if ($editingId)
                <button type="button" wire:click="cancel" class="rounded px-4 py-2 text-sm text-stone-600 hover:text-stone-900">
                    Cancelar
                </button>
            @endif
        </div>
    </form>

    <div class="space-y-3">
        @forelse ($this->farms as $farm)
            <div class="flex items-center justify-between rounded border border-stone-200 p-4">
                <div>
                    <p class="font-medium">{{ $farm->name }}</p>
                    <p class="text-sm text-stone-500">
                        {{ collect([$farm->address, $farm->district, $farm->province])->filter()->implode(', ') ?: 'Sem endereço registado' }}
                    </p>
                </div>
                <div class="flex gap-3 text-sm">
                    <button wire:click="edit({{ $farm->id }})" class="text-green-700 hover:underline">Editar</button>
                    <button wire:click="delete({{ $farm->id }})" wire:confirm="Remover esta propriedade?" class="text-red-600 hover:underline">Remover</button>
                </div>
            </div>
        @empty
            <p class="text-sm text-stone-500">Ainda não tem propriedades registadas.</p>
        @endforelse
    </div>
</x-layouts.app>