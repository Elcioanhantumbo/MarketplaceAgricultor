<?php

namespace App\Livewire\Auth;

use App\Support\MozambiquePhone;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $phone = '';

    public string $password = '';

    public function login(): void
    {
        $this->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $normalizedPhone = MozambiquePhone::normalize($this->phone);

        if (! $normalizedPhone || ! Auth::attempt(['phone' => $normalizedPhone, 'password' => $this->password])) {
            $this->addError('phone', 'Telefone ou palavra-passe incorrectos.');

            return;
        }

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