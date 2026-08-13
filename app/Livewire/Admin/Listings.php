<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\RequiresAdmin;
use App\Models\ProductListing;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Listings extends Component
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
        $listings = ProductListing::with(['product', 'producer.user'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.listings', ['listings' => $listings]);
    }
}