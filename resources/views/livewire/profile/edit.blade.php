<div>
    <h1 class="mb-1 text-lg font-semibold">Editar perfil</h1>
    <p class="mb-6 text-sm text-stone-500">Telefone: {{ auth()->user()->phone }} (verificado)</p>

    @if ($saved)
        <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
            Perfil actualizado.
        </div>
    @endif

    <form wire:submit="save" class="max-w-md space-y-4">
        <div x-data="avatarUploader()" class="flex items-center gap-4">
            <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-full bg-stone-100">
                <template x-if="preview">
                    <img :src="preview" class="h-full w-full object-cover" alt="">
                </template>
                @if ($avatar_path)
                    <img x-show="!preview" src="{{ asset('storage/'.$avatar_path) }}" class="h-full w-full object-cover" alt="">
                @endif
                <div x-show="compressing" class="absolute inset-0 flex items-center justify-center bg-white/70 text-xs text-stone-500">…</div>
            </div>
            <div>
                <label for="avatar-input" class="cursor-pointer rounded border border-stone-300 px-3 py-1.5 text-sm hover:border-green-600 hover:text-green-700">
                    Alterar foto
                </label>
                <input id="avatar-input" type="file" accept="image/*" @change="handleFile" class="hidden">
                <p class="mt-1 text-xs text-stone-400">Comprimida automaticamente antes do envio.</p>
                @error('avatar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium" for="name">Nome</label>
            <input wire:model="name" id="name" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        @if (auth()->user()->role === 'producer')
            <div>
                <label class="block text-sm font-medium" for="business_name">Nome do negócio (opcional)</label>
                <input wire:model="business_name" id="business_name" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            </div>
        @elseif (auth()->user()->role === 'buyer')
            <div>
                <label class="block text-sm font-medium" for="business_name">Nome do negócio (opcional)</label>
                <input wire:model="business_name" id="business_name" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            </div>
            <div>
                <label class="block text-sm font-medium" for="buyer_type">Tipo de comprador</label>
                <select wire:model="buyer_type" id="buyer_type" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
                    <option value="">— Seleccionar —</option>
                    <option value="restaurante">Restaurante</option>
                    <option value="hotel">Hotel</option>
                    <option value="supermercado">Supermercado</option>
                    <option value="grossista">Grossista</option>
                    <option value="agro_processador">Agro-processador</option>
                    <option value="instituicao">Instituição</option>
                </select>
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium" for="address">Endereço</label>
            <input wire:model="address" id="address" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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

        <div>
            <label class="block text-sm font-medium" for="bio">Sobre (opcional)</label>
            <textarea wire:model="bio" id="bio" rows="3" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600"></textarea>
        </div>

        <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800" wire:loading.attr="disabled">
            Guardar
        </button>
    </form>
</div>
