<x-layouts.guest title="Iniciar sessão — AgroLink MZ">
    <h1 class="mb-6 text-lg font-semibold">Iniciar sessão</h1>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="block text-sm font-medium" for="phone">Telefone</label>
            <input wire:model="phone" id="phone" type="tel" placeholder="84 123 4567" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="password">Palavra-passe</label>
            <input wire:model="password" id="password" type="password" class="mt-1 w-full rounded border-stone-300 focus:border-green-600 focus:ring-green-600">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800" wire:loading.attr="disabled">
            Entrar
        </button>
    </form>

    <p class="mt-4 text-center text-sm text-stone-500">
        Ainda não tem conta? <a href="{{ route('registo') }}" class="text-green-700 hover:underline" wire:navigate>Criar conta</a>
    </p>
</x-layouts.guest>