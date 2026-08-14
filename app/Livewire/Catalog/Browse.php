<?php

namespace App\Livewire\Catalog;

use App\Models\Category;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Categorias e produtos — AgroLink MZ')]
class Browse extends Component
{
    public function render()
    {
        return view('livewire.catalog.browse', [
            'categories' => Category::with('products')->orderBy('name')->get(),
        ]);
    }
}