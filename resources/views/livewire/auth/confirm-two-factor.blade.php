<div>
    <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-amber-700">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
    </div>

    <h1 class="text-lg font-semibold text-stone-900">Confirmar acesso administrativo</h1>
    <p class="mt-1 mb-6 text-sm text-stone-500">
        Por segurança, o acesso ao painel administrativo exige um segundo código. Enviámos um código de
        {{ config('otp.code_length') }} dígitos por SMS para <strong class="text-stone-700">{{ auth()->user()->phone }}</strong>.
    </p>

    <form wire:submit="verify" class="space-y-4">
        <x-ui.field name="code" label="Código">
            <x-ui.input name="code" wire:model="code" type="text" inputmode="numeric" maxlength="{{ config('otp.code_length') }}"
                        class="text-center text-lg tracking-[0.5em]" autofocus />
        </x-ui.field>

        <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="verify">Confirmar</span>
            <span wire:loading wire:target="verify">A confirmar…</span>
        </x-ui.button>
    </form>

    <button wire:click="resend" class="mt-4 w-full text-center text-sm font-medium text-green-700 hover:underline" wire:loading.attr="disabled">
        Reenviar código
    </button>

    @if (app()->environment('local'))
        <p class="mt-4 rounded-lg bg-stone-50 px-3 py-2 text-center text-xs text-stone-400">Ambiente local: veja o código em storage/logs/laravel.log.</p>
    @endif
</div>
