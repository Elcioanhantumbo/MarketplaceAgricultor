<?php

namespace App\Livewire\Orders;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BuyerIndex extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::user()->role === 'buyer', 403);
    }

    public function render()
    {
        return view('livewire.orders.buyer-index', [
            'orders' => Auth::user()->ordersAsBuyer()->with('items.productListing.product')->latest()->paginate(10),
        ]);
    }
}