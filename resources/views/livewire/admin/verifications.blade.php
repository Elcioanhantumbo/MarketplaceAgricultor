<div>
    <h1 class="mb-1 text-lg font-semibold">Verificação de perfis</h1>
    <x-admin-nav />
    <p class="mb-4 text-sm text-stone-500">RN16 — o selo de verificado só é atribuído após revisão administrativa.</p>

    <div class="space-y-3">
        @forelse ($this->pending as $profile)
            <div class="flex items-center justify-between rounded border border-stone-200 p-4">
                <div>
                    <p class="font-medium">{{ $profile->user->name }} <span class="text-stone-400">({{ ucfirst($profile->user->role) }})</span></p>
                    <p class="text-sm text-stone-500">{{ $profile->address }}, {{ collect([$profile->district, $profile->province])->filter()->implode(', ') }}</p>
                </div>
                <button wire:click="verify({{ $profile->id }})" wire:confirm="Marcar este perfil como verificado?" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">
                    Verificar
                </button>
            </div>
        @empty
            <p class="text-sm text-stone-500">Sem perfis pendentes de verificação.</p>
        @endforelse
    </div>
</div>
