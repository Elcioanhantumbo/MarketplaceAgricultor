<?php

namespace App\Livewire\Listings;

use App\Models\Category;
use App\Models\ProductListing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Manage extends Component
{
    public ?int $editingId = null;

    public ?int $farm_id = null;

    public ?int $product_id = null;

    public string $quantity = '';

    public string $unit = '';

    public string $price = '';

    public string $available_from = '';

    public string $available_until = '';

    public function mount(): void
    {
        Gate::authorize('create', ProductListing::class);
    }

    #[Computed]
    public function listings()
    {
        return Auth::user()->producer->productListings()->with('product')->latest()->get();
    }

    #[Computed]
    public function farms()
    {
        return Auth::user()->producer->farms;
    }

    #[Computed]
    public function categories()
    {
        return Category::with('products')->orderBy('name')->get();
    }

    public function updatedProductId(): void
    {
        if ($this->product_id && ! $this->unit) {
            $this->unit = \App\Models\Product::find($this->product_id)?->default_unit ?? '';
        }
    }

    protected function rules(): array
    {
        return [
            'farm_id' => 'nullable|exists:farms,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0.01',
            'available_from' => 'required|date',
            'available_until' => 'required|date|after_or_equal:available_from',
        ];
    }

    public function edit(int $listingId): void
    {
        $listing = ProductListing::findOrFail($listingId);
        Gate::authorize('update', $listing);

        if ($listing->status !== 'disponivel') {
            $this->addError('editingId', 'Só pode editar ofertas ainda disponíveis. Para o resto, encerre e crie uma nova.');

            return;
        }

        $this->editingId = $listing->id;
        $this->farm_id = $listing->farm_id;
        $this->product_id = $listing->product_id;
        $this->quantity = (string) $listing->quantity;
        $this->unit = $listing->unit;
        $this->price = (string) $listing->price;
        $this->available_from = $listing->available_from->toDateString();
        $this->available_until = $listing->available_until->toDateString();
    }

    public function cancel(): void
    {
        $this->reset([
            'editingId', 'farm_id', 'product_id', 'quantity', 'unit',
            'price', 'available_from', 'available_until',
        ]);
    }

    public function save(): void
    {
        $this->validate();

        $farm = $this->farm_id ? Auth::user()->producer->farms()->find($this->farm_id) : null;

        $data = [
            'farm_id' => $this->farm_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'price' => $this->price,
            'available_from' => $this->available_from,
            'available_until' => $this->available_until,
            'latitude' => $farm?->latitude,
            'longitude' => $farm?->longitude,
        ];

        if ($this->editingId) {
            $listing = ProductListing::findOrFail($this->editingId);
            Gate::authorize('update', $listing);
            $listing->update($data);
        } else {
            Gate::authorize('create', ProductListing::class);
            Auth::user()->producer->productListings()->create($data + ['status' => 'disponivel']);
        }

        unset($this->listings);
        $this->cancel();
    }

    /** RN08 — encerrar manualmente uma oferta (equivalente a "pausar/encerrar" da secção 11.1). */
    public function close(int $listingId): void
    {
        $listing = ProductListing::findOrFail($listingId);
        Gate::authorize('update', $listing);

        if (in_array($listing->status, ['disponivel', 'reservado'], true)) {
            $listing->update(['status' => 'expirado']);
        }

        unset($this->listings);
    }

    public function render()
    {
        return view('livewire.listings.manage');
    }
}