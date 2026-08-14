<?php

namespace App\Livewire\Orders;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pedidos recebidos — AgroLink MZ')]
class ProducerIndex extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::user()->role === 'producer', 403);
    }

    public function render()
    {
        return view('livewire.orders.producer-index', [
            'orders' => Auth::user()->producer->orders()->with('items.productListing.product', 'buyer')->latest()->paginate(10),
        ]);
    }
}