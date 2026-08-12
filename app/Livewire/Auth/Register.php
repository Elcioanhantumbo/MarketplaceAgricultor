<?php

namespace App\Livewire\Auth;

use App\Models\Buyer;
use App\Models\Producer;
use App\Models\User;
use App\Services\OtpService;
use App\Support\MozambiquePhone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $role = 'producer';

    public string $password = '';

    public string $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'role' => 'required|in:producer,buyer',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function register(OtpService $otpService): void
    {
        $this->validate();

        $normalizedPhone = MozambiquePhone::normalize($this->phone);

        if (! $normalizedPhone) {
            $this->addError('phone', 'Introduza um número moçambicano válido (ex.: 84 123 4567).');

            return;
        }

        if (User::where('phone', $normalizedPhone)->exists()) {
            $this->addError('phone', 'Este número já está registado.');

            return;
        }

        $user = DB::transaction(function () use ($normalizedPhone) {
            $user = User::create([
                'name' => $this->name,
                'phone' => $normalizedPhone,
                'password' => $this->password,
                'role' => $this->role,
                'status' => 'pending',
            ]);

            if ($this->role === 'producer') {
                Producer::create(['user_id' => $user->id]);
            } else {
                Buyer::create(['user_id' => $user->id]);
            }

            return $user;
        });

        Auth::login($user);
        $otpService->generate($user);

        $this->redirectRoute('verificar-telefone', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}