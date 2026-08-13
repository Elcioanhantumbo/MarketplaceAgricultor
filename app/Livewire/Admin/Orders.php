<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\RequiresAdmin;
use App\Models\Order;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination, RequiresAdmin;

    #[Url]
    public string $status = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $orders = Order::with(['buyer', 'producer.user', 'items.productListing.product'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.orders', ['orders' => $orders]);
    }
}