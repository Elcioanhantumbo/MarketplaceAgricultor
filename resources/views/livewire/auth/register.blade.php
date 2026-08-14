<div>
    <h1 class="mb-1 text-lg font-semibold">Criar conta</h1>
    <p class="mb-6 text-sm text-stone-500">Produtores e compradores registam-se aqui. Transportadores participam de forma assistida no piloto.</p>

    <form wire:submit="register" class="space-y-4">
        <div>
            <label class="block text-sm font-medium" for="name">Nome</label>
            <input wire:model="name" id="name" type="text" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="phone">Telefone</label>
            <input wire:model="phone" id="phone" type="tel" placeholder="84 123 4567" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Sou</label>
            <div class="mt-1 flex gap-4 text-sm">
                <label class="flex items-center gap-1">
                    <input wire:model="role" type="radio" value="producer"> Produtor
                </label>
                <label class="flex items-center gap-1">
                    <input wire:model="role" type="radio" value="buyer"> Comprador
                </label>
            </div>
            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="password">Palavra-passe</label>
            <input wire:model="password" id="password" type="password" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="password_confirmation">Confirmar palavra-passe</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
        </div>

        <button type="submit" class="w-full rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800" wire:loading.attr="disabled">
            Criar conta
        </button>
    </form>

    <p class="mt-4 text-center text-sm text-stone-500">
        Já tem conta? <a href="{{ route('login') }}" class="text-green-700 hover:underline" wire:navigate>Iniciar sessão</a>
    </p>
</div>
