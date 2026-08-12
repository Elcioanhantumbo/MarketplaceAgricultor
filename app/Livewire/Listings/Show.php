<?php

namespace App\Livewire\Listings;

use App\Models\ProductListing;
use Livewire\Component;

class Show extends Component
{
    public ProductListing $listing;

    public function mount(ProductListing $listing): void
    {
        $this->listing = $listing->load(['product.category', 'producer.user.profile', 'farm']);
    }

    public function render()
    {
        return view('livewire.listings.show');
    }
}