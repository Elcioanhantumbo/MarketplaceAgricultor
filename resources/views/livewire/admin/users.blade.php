<x-layouts.app title="Utilizadores — AgroLink MZ">
    <h1 class="mb-1 text-lg font-semibold">Utilizadores</h1>
    <x-admin-nav />

    <div class="mb-4 flex flex-wrap gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Nome ou telefone" class="rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
        <select wire:model.live="role" class="rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
            <option value="">Todos os papéis</option>
            <option value="producer">Produtor</option>
            <option value="buyer">Comprador</option>
            <option value="transporter">Transportador</option>
            <option value="operator">Operador</option>
            <option value="admin">Administrador</option>
        </select>
    </div>

    <div class="space-y-2">
        @foreach ($users as $user)
            <div class="flex items-center justify-between rounded border border-stone-200 p-3 text-sm">
                <div>
                    <p class="font-medium">{{ $user->name }}</p>
                    <p class="text-stone-500">{{ $user->phone }} · {{ ucfirst($user->role) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $user->status === 'blocked' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-800' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                    @if ($user->id !== auth()->id())
                        <button wire:click="toggleBlocked({{ $user->id }})" wire:confirm="{{ $user->status === 'blocked' ? 'Desbloquear' : 'Bloquear' }} esta conta?" class="{{ $user->status === 'blocked' ? 'text-green-700' : 'text-red-600' }} hover:underline">
                            {{ $user->status === 'blocked' ? 'Desbloquear' : 'Bloquear' }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.app>