<div>
    <h1 class="text-lg font-semibold text-stone-900">Criar conta</h1>
    <p class="mt-1 mb-6 text-sm text-stone-500">Produtores e compradores registam-se aqui. Transportadores participam de forma assistida no piloto.</p>

    <form wire:submit="register" class="space-y-4">
        <x-ui.field name="name" label="Nome" required>
            <x-ui.input name="name" wire:model="name" type="text" autofocus />
        </x-ui.field>

        <x-ui.field name="phone" label="Telefone" required>
            <x-ui.input name="phone" wire:model="phone" type="tel" inputmode="tel" placeholder="84 123 4567" />
        </x-ui.field>

        <x-ui.field name="role" label="Sou" required>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-stone-300 px-3 py-2.5 font-medium text-stone-600 transition has-[:checked]:border-green-600 has-[:checked]:bg-green-50 has-[:checked]:text-green-800">
                    <input wire:model="role" type="radio" value="producer" class="sr-only">
                    Produtor
                </label>
                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-stone-300 px-3 py-2.5 font-medium text-stone-600 transition has-[:checked]:border-green-600 has-[:checked]:bg-green-50 has-[:checked]:text-green-800">
                    <input wire:model="role" type="radio" value="buyer" class="sr-only">
                    Comprador
                </label>
            </div>
        </x-ui.field>

        <x-ui.field name="password" label="Palavra-passe" hint="Pelo menos 8 caracteres." required>
            <x-ui.input name="password" wire:model="password" type="password" />
        </x-ui.field>

        <x-ui.field name="password_confirmation" label="Confirmar palavra-passe" required>
            <x-ui.input name="password_confirmation" wire:model="password_confirmation" type="password" />
        </x-ui.field>

        <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="register">Criar conta</span>
            <span wire:loading wire:target="register">A criar…</span>
        </x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-500">
        Já tem conta? <a href="{{ route('login') }}" class="font-medium text-green-700 hover:underline" wire:navigate>Iniciar sessão</a>
    </p>
</div>
