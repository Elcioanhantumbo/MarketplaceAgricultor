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

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-200 text-left text-xs text-stone-500">
                    <th class="py-2">Nome</th>
                    <th class="py-2">Telefone</th>
                    <th class="py-2">Papel</th>
                    <th class="py-2">Estado</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b border-stone-100">
                        <td class="py-2">{{ $user->name }}</td>
                        <td class="py-2">{{ $user->phone }}</td>
                        <td class="py-2">{{ ucfirst($user->role) }}</td>
                        <td class="py-2">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $user->status === 'blocked' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="py-2 text-right">
                            @if ($user->id !== auth()->id())
                                <button wire:click="toggleBlocked({{ $user->id }})" wire:confirm="{{ $user->status === 'blocked' ? 'Desbloquear' : 'Bloquear' }} esta conta?" class="text-sm {{ $user->status === 'blocked' ? 'text-green-700' : 'text-red-600' }} hover:underline">
                                    {{ $user->status === 'blocked' ? 'Desbloquear' : 'Bloquear' }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.app>