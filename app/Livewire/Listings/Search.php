<?php

namespace App\Livewire\Listings;

use App\Models\Category;
use App\Models\Location;
use App\Models\ProductListing;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pesquisar ofertas — AgroLink MZ')]
class Search extends Component
{
    use WithPagination;

    #[Url]
    public ?int $category_id = null;

    #[Url]
    public string $min_price = '';

    #[Url]
    public string $max_price = '';

    #[Url]
    public string $min_quantity = '';

    #[Url]
    public ?int $location_id = null;

    #[Url]
    public int $radius_km = 50;

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ProductListing::query()->available()->with(['product.category', 'producer.user', 'farm']);

        if ($this->category_id) {
            $query->whereHas('product', fn ($q) => $q->where('category_id', $this->category_id));
        }

        if ($this->min_price !== '') {
            $query->where('price', '>=', $this->min_price);
        }

        if ($this->max_price !== '') {
            $query->where('price', '<=', $this->max_price);
        }

        if ($this->min_quantity !== '') {
            $query->where('quantity', '>=', $this->min_quantity);
        }

        if ($this->location_id && $location = Location::find($this->location_id)) {
            $query->nearby((float) $location->latitude, (float) $location->longitude, (float) $this->radius_km);
        } else {
            $query->latest();
        }

        return view('livewire.listings.search', [
            'listings' => $query->paginate(12),
            'categories' => Category::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ]);
    }
}