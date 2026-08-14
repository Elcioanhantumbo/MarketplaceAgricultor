<?php

namespace App\Livewire\Auth;

use App\Support\MozambiquePhone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Iniciar sessão — AgroLink MZ')]
class Login extends Component
{
    public string $phone = '';

    public string $password = '';

    /** Secção 22 — limita tentativas de força bruta por telefone+IP. */
    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->phone).'|'.request()->ip());
    }

    public function login(): void
    {
        $this->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('phone', "Demasiadas tentativas. Tente novamente dentro de {$seconds} segundos.");

            return;
        }

        $normalizedPhone = MozambiquePhone::normalize($this->phone);

        if (! $normalizedPhone || ! Auth::attempt(['phone' => $normalizedPhone, 'password' => $this->password])) {
            RateLimiter::hit($key, decaySeconds: 60);
            $this->addError('phone', 'Telefone ou palavra-passe incorrectos.');

            return;
        }

        RateLimiter::clear($key);

        if (Auth::user()->status === 'blocked') {
            Auth::logout();
            $this->addError('phone', 'Esta conta está bloqueada. Contacte o suporte.');

            return;
        }

        session()->regenerate();

        $this->redirectRoute(
            Auth::user()->phone_verified_at ? 'painel' : 'verificar-telefone',
            navigate: true,
        );
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}