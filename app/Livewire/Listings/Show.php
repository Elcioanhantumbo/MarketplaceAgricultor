<?php

namespace App\Livewire\Listings;

use App\Exceptions\OrderWorkflowException;
use App\Models\ProductListing;
use App\Services\OrderWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public ProductListing $listing;

    public string $quantity = '';

    public string $delivery_method = 'comprador_levanta';

    public function mount(ProductListing $listing): void
    {
        $this->listing = $listing->load(['product.category', 'producer.user.profile', 'farm']);
    }

    public function order(OrderWorkflowService $workflow): void
    {
        $this->validate([
            'quantity' => 'required|numeric|min:0.01',
            'delivery_method' => 'required|in:comprador_levanta,produtor_entrega,transporte_intermediado',
        ]);

        try {
            $order = $workflow->create(Auth::user(), $this->listing, (float) $this->quantity, $this->delivery_method);
        } catch (OrderWorkflowException $e) {
            $this->addError('quantity', $e->getMessage());

            return;
        }

        $this->redirectRoute('pedidos.show', $order, navigate: true);
    }

    public function render()
    {
        return view('livewire.listings.show');
    }
}