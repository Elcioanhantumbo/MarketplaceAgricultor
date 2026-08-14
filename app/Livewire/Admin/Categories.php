<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\RequiresAdmin;
use App\Models\Category;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Categorias — AgroLink MZ')]
class Categories extends Component
{
    use RequiresAdmin;

    public string $category_name = '';

    public ?int $product_category_id = null;

    public string $product_name = '';

    public string $product_unit = '';

    #[Computed]
    public function categories()
    {
        return Category::with('products')->orderBy('name')->get();
    }

    public function addCategory(AuditLogger $audit): void
    {
        $this->validate(['category_name' => 'required|string|max:255']);

        $category = Category::create(['name' => $this->category_name, 'slug' => Str::slug($this->category_name)]);
        $audit->log(Auth::user(), 'category.created', $category, [], ['name' => $category->name]);

        $this->reset('category_name');
        unset($this->categories);
    }

    public function addProduct(AuditLogger $audit): void
    {
        $this->validate([
            'product_category_id' => 'required|exists:categories,id',
            'product_name' => 'required|string|max:255',
            'product_unit' => 'required|string|max:50',
        ]);

        $product = Category::findOrFail($this->product_category_id)->products()->create([
            'name' => $this->product_name,
            'slug' => Str::slug($this->product_name),
            'default_unit' => $this->product_unit,
        ]);
        $audit->log(Auth::user(), 'product.created', $product, [], ['name' => $product->name]);

        $this->reset('product_category_id', 'product_name', 'product_unit');
        unset($this->categories);
    }

    public function render()
    {
        return view('livewire.admin.categories');
    }
}