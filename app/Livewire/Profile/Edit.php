<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Editar perfil — AgroLink MZ')]
class Edit extends Component
{
    use WithFileUploads;

    public string $name = '';

    public $avatar = null;

    public ?string $avatar_path = null;

    public string $business_name = '';

    public ?string $buyer_type = null;

    public string $bio = '';

    public string $address = '';

    public string $district = '';

    public string $province = '';

    public bool $saved = false;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->business_name = $user->producer?->business_name ?? $user->buyer?->business_name ?? '';
        $this->buyer_type = $user->buyer?->buyer_type;

        $profile = $user->profile;
        $this->bio = $profile?->bio ?? '';
        $this->address = $profile?->address ?? '';
        $this->district = $profile?->district ?? '';
        $this->province = $profile?->province ?? '';
        $this->avatar_path = $profile?->avatar_path;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|max:1024',
            'business_name' => 'nullable|string|max:255',
            'buyer_type' => 'nullable|in:restaurante,hotel,supermercado,grossista,agro_processador,instituicao',
            'bio' => 'nullable|string|max:1000',
            'address' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
        ];
    }

    /** Secção 19 — foto de perfil, já comprimida no navegador antes do envio. */
    public function updatedAvatar(): void
    {
        $this->validateOnly('avatar');

        if ($this->avatar_path) {
            Storage::disk('public')->delete($this->avatar_path);
        }

        $this->avatar_path = $this->avatar->store('avatars', 'public');
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $user->update(['name' => $this->name]);

        $user->profile()->updateOrCreate(['user_id' => $user->id], [
            'avatar_path' => $this->avatar_path,
            'bio' => $this->bio,
            'address' => $this->address,
            'district' => $this->district,
            'province' => $this->province,
        ]);

        if ($user->role === 'producer') {
            $user->producer->update(['business_name' => $this->business_name]);
        } elseif ($user->role === 'buyer') {
            $user->buyer->update([
                'business_name' => $this->business_name,
                'buyer_type' => $this->buyer_type,
            ]);
        }

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.profile.edit');
    }
}