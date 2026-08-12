<?php

namespace App\Livewire\Auth;

use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class VerifyOtp extends Component
{
    public string $code = '';

    public function verify(OtpService $otpService): void
    {
        $this->validate(['code' => 'required|string|size:'.config('otp.code_length')]);

        $user = Auth::user();

        if ($otpService->verify($user, $this->code)) {
            $this->redirectRoute('painel', navigate: true);

            return;
        }

        $this->addError('code', 'Código inválido ou expirado. Tente novamente ou peça um novo código.');
    }

    public function resend(OtpService $otpService): void
    {
        $user = Auth::user();
        $cacheKey = "otp-resend:{$user->id}";

        if (Cache::has($cacheKey)) {
            $this->addError('code', 'Aguarde um pouco antes de pedir um novo código.');

            return;
        }

        $otpService->generate($user);
        Cache::put($cacheKey, true, config('otp.resend_cooldown_seconds'));

        $this->dispatch('otp-resent');
    }

    public function render()
    {
        return view('livewire.auth.verify-otp');
    }
}