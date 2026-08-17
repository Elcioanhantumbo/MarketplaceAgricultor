<div>
    <x-ui.page-header title="Editar perfil" subtitle="Telefone: {{ auth()->user()->phone }} (verificado)" />

    @if ($saved)
        <x-ui.alert type="success" class="mb-4">Perfil actualizado.</x-ui.alert>
    @endif

    <x-ui.card class="max-w-lg">
        <form wire:submit="save" class="space-y-5">
            <div x-data="avatarUploader()" class="flex items-center gap-4">
                <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-full bg-stone-100 ring-2 ring-stone-100">
                    <template x-if="preview">
                        <img :src="preview" class="h-full w-full object-cover" alt="">
                    </template>
                    @if ($avatar_path)
                        <img x-show="!preview" src="{{ asset('storage/'.$avatar_path) }}" class="h-full w-full object-cover" alt="">
                    @endif
                    <div x-show="compressing" class="absolute inset-0 flex items-center justify-center bg-white/70 text-xs text-stone-500">…</div>
                </div>
                <div>
                    <label for="avatar-input" class="cursor-pointer rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-medium text-stone-700 shadow-sm hover:border-green-600 hover:text-green-700">
                        Alterar foto
                    </label>
                    <input id="avatar-input" type="file" accept="image/*" @change="handleFile" class="hidden">
                    <p class="mt-1.5 text-xs text-stone-400">Comprimida automaticamente antes do envio.</p>
                    @error('avatar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <x-ui.field name="name" label="Nome" required>
                <x-ui.input name="name" wire:model="name" type="text" />
            </x-ui.field>

            @if (auth()->user()->role === 'producer')
                <x-ui.field name="business_name" label="Nome do negócio" hint="Opcional.">
                    <x-ui.input name="business_name" wire:model="business_name" type="text" />
                </x-ui.field>
            @elseif (auth()->user()->role === 'buyer')
                <x-ui.field name="business_name" label="Nome do negócio" hint="Opcional.">
                    <x-ui.input name="business_name" wire:model="business_name" type="text" />
                </x-ui.field>
                <x-ui.field name="buyer_type" label="Tipo de comprador">
                    <x-ui.select name="buyer_type" wire:model="buyer_type">
                        <option value="">— Seleccionar —</option>
                        <option value="restaurante">Restaurante</option>
                        <option value="hotel">Hotel</option>
                        <option value="supermercado">Supermercado</option>
                        <option value="grossista">Grossista</option>
                        <option value="agro_processador">Agro-processador</option>
                        <option value="instituicao">Instituição</option>
                    </x-ui.select>
                </x-ui.field>
            @endif

            <x-ui.field name="address" label="Endereço" required>
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

            <x-ui.field name="bio" label="Sobre" hint="Opcional.">
                <x-ui.textarea name="bio" wire:model="bio" rows="3" />
            </x-ui.field>

            <x-ui.button type="submit" wire:loading.attr="disabled">Guardar</x-ui.button>
        </form>
    </x-ui.card>
</div>
