<?php

namespace App\Livewire\Catalog;

use App\Models\Category;
use Livewire\Component;

class Browse extends Component
{
    public function render()
    {
        return view('livewire.catalog.browse', [
            'categories' => Category::with('products')->orderBy('name')->get(),
        ]);
    }
}