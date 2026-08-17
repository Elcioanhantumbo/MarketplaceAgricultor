<div>
    <x-ui.page-header title="Verificação de perfis" subtitle="O selo de verificado só é atribuído após revisão administrativa." />
    <x-admin-nav />

    <div class="space-y-3">
        @forelse ($this->pending as $profile)
            <div class="flex items-center justify-between rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="font-medium text-stone-900">{{ $profile->user->name }} <span class="text-stone-400">({{ ucfirst($profile->user->role) }})</span></p>
                    <p class="text-sm text-stone-500">{{ $profile->address }}, {{ collect([$profile->district, $profile->province])->filter()->implode(', ') }}</p>
                </div>
                <x-ui.button size="sm" wire:click="verify({{ $profile->id }})" wire:confirm="Marcar este perfil como verificado?">
                    Verificar
                </x-ui.button>
            </div>
        @empty
            <x-ui.empty-state title="Sem perfis pendentes de verificação" />
        @endforelse
    </div>
</div>
