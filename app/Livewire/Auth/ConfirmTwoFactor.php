<?php

namespace App\Livewire\Auth;

use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ConfirmTwoFactor extends Component
{
    /** Secção 22 — segundo factor (OTP por SMS) exigido a admin/operador antes do painel. */
    private const string PURPOSE = 'admin_2fa';

    public string $code = '';

    public function mount(OtpService $otpService): void
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['admin', 'operator'], true), 403);

        if (session('two_factor_confirmed_at')) {
            $this->redirect($this->intendedUrl(), navigate: true);

            return;
        }

        $cacheKey = 'admin-2fa-sent:'.$user->id;
        if (! Cache::has($cacheKey)) {
            $otpService->generate($user, self::PURPOSE);
            Cache::put($cacheKey, true, config('otp.resend_cooldown_seconds'));
        }
    }

    private function intendedUrl(): string
    {
        return session()->pull('url.intended', route('admin.dashboard'));
    }

    public function verify(OtpService $otpService): void
    {
        $this->validate(['code' => 'required|string|size:'.config('otp.code_length')]);

        if (! $otpService->verify(Auth::user(), $this->code, self::PURPOSE)) {
            $this->addError('code', 'Código inválido ou expirado. Tente novamente ou peça um novo código.');

            return;
        }

        session(['two_factor_confirmed_at' => now()->toDateTimeString()]);

        $this->redirect($this->intendedUrl(), navigate: true);
    }

    public function resend(OtpService $otpService): void
    {
        $user = Auth::user();
        $cacheKey = 'admin-2fa-resend:'.$user->id;

        if (Cache::has($cacheKey)) {
            $this->addError('code', 'Aguarde um pouco antes de pedir um novo código.');

            return;
        }

        $otpService->generate($user, self::PURPOSE);
        Cache::put($cacheKey, true, config('otp.resend_cooldown_seconds'));

        $this->dispatch('otp-resent');
    }

    public function render()
    {
        return view('livewire.auth.confirm-two-factor');
    }
}