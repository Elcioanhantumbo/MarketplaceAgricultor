<div>
    <h1 class="text-lg font-semibold text-stone-900">Iniciar sessão</h1>
    <p class="mt-1 mb-6 text-sm text-stone-500">Entre com o telefone e a palavra-passe da sua conta.</p>

    <form wire:submit="login" class="space-y-4">
        <x-ui.field name="phone" label="Telefone" required>
            <x-ui.input name="phone" wire:model="phone" type="tel" inputmode="tel" placeholder="84 123 4567" autofocus />
        </x-ui.field>

        <x-ui.field name="password" label="Palavra-passe" required>
            <x-ui.input name="password" wire:model="password" type="password" />
        </x-ui.field>

        <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Entrar</span>
            <span wire:loading wire:target="login">A entrar…</span>
        </x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-500">
        Ainda não tem conta? <a href="{{ route('registo') }}" class="font-medium text-green-700 hover:underline" wire:navigate>Criar conta</a>
    </p>
</div>
