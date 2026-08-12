<?php

namespace App\Livewire\Farms;

use App\Models\Farm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Manage extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $address = '';

    public string $district = '';

    public string $province = '';

    public ?string $latitude = null;

    public ?string $longitude = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', Farm::class);
    }

    #[Computed]
    public function farms()
    {
        return Auth::user()->producer->farms()->latest()->get();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    public function edit(int $farmId): void
    {
        $farm = Farm::findOrFail($farmId);
        Gate::authorize('update', $farm);

        $this->editingId = $farm->id;
        $this->name = $farm->name;
        $this->address = $farm->address ?? '';
        $this->district = $farm->district ?? '';
        $this->province = $farm->province ?? '';
        $this->latitude = $farm->latitude;
        $this->longitude = $farm->longitude;
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'address', 'district', 'province', 'latitude', 'longitude']);
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'address' => $this->address ?: null,
            'district' => $this->district ?: null,
            'province' => $this->province ?: null,
            'latitude' => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
        ];

        if ($this->editingId) {
            $farm = Farm::findOrFail($this->editingId);
            Gate::authorize('update', $farm);
            $farm->update($data);
        } else {
            Gate::authorize('create', Farm::class);
            Auth::user()->producer->farms()->create($data);
        }

        unset($this->farms);
        $this->cancel();
    }

    public function delete(int $farmId): void
    {
        $farm = Farm::findOrFail($farmId);
        Gate::authorize('delete', $farm);
        $farm->delete();

        unset($this->farms);
    }

    public function render()
    {
        return view('livewire.farms.manage');
    }
}