<div>
    <x-ui.page-header title="Utilizadores" />
    <x-admin-nav />

    <div class="mb-4 flex flex-wrap gap-3">
        <x-ui.input wire:model.live.debounce.300ms="search" type="text" placeholder="Nome ou telefone" class="max-w-xs text-sm" />
        <x-ui.select wire:model.live="role" class="max-w-xs text-sm">
            <option value="">Todos os papéis</option>
            <option value="producer">Produtor</option>
            <option value="buyer">Comprador</option>
            <option value="transporter">Transportador</option>
            <option value="operator">Operador</option>
            <option value="admin">Administrador</option>
        </x-ui.select>
    </div>

    <div class="space-y-2">
        @forelse ($users as $user)
            <div class="flex items-center justify-between rounded-xl border border-stone-200 bg-white p-4 text-sm shadow-sm">
                <div>
                    <p class="font-medium text-stone-900">{{ $user->name }}</p>
                    <p class="text-stone-500">{{ $user->phone }} · {{ ucfirst($user->role) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-ui.badge :color="$user->status === 'blocked' ? 'red' : 'green'">{{ ucfirst($user->status) }}</x-ui.badge>
                    @if ($user->id !== auth()->id())
                        <button wire:click="toggleBlocked({{ $user->id }})" wire:confirm="{{ $user->status === 'blocked' ? 'Desbloquear' : 'Bloquear' }} esta conta?" class="font-medium {{ $user->status === 'blocked' ? 'text-green-700' : 'text-red-600' }} hover:underline">
                            {{ $user->status === 'blocked' ? 'Desbloquear' : 'Bloquear' }}
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Nenhum utilizador encontrado" />
        @endforelse
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
