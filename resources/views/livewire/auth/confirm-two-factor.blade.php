<x-layouts.guest title="Confirmar acesso — AgroLink MZ">
    <h1 class="mb-1 text-lg font-semibold">Confirmar acesso administrativo</h1>
    <p class="mb-6 text-sm text-stone-500">
        Por segurança, o acesso ao painel administrativo exige um segundo código. Enviámos um código de
        {{ config('otp.code_length') }} dígitos por SMS para <strong>{{ auth()->user()->phone }}</strong>.
    </p>

    <form wire:submit="verify" class="space-y-4">
        <div>
            <label class="block text-sm font-medium" for="code">Código</label>
            <input wire:model="code" id="code" type="text" inputmode="numeric" maxlength="{{ config('otp.code_length') }}"
                   class="mt-1 w-full rounded border-stone-300 text-center text-lg tracking-widest focus:border-green-600 focus:ring-green-600">
            @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800" wire:loading.attr="disabled">
            Confirmar
        </button>
    </form>

    <button wire:click="resend" class="mt-4 w-full text-center text-sm text-green-700 hover:underline" wire:loading.attr="disabled">
        Reenviar código
    </button>

    @if (app()->environment('local'))
        <p class="mt-4 text-center text-xs text-stone-400">Ambiente local: veja o código em storage/logs/laravel.log.</p>
    @endif
</x-layouts.guest>