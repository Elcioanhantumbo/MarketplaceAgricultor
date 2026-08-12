<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    public string $name = '';

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
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'buyer_type' => 'nullable|in:restaurante,hotel,supermercado,grossista,agro_processador,instituicao',
            'bio' => 'nullable|string|max:1000',
            'address' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $user->update(['name' => $this->name]);

        $user->profile()->updateOrCreate(['user_id' => $user->id], [
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