<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Categorias/produtos de arranque do piloto (secção 24 do business plan).
     */
    public function run(): void
    {
        $catalog = [
            'Cereais' => [
                ['name' => 'Milho', 'default_unit' => 'kg'],
                ['name' => 'Arroz', 'default_unit' => 'kg'],
            ],
            'Hortícolas' => [
                ['name' => 'Tomate', 'default_unit' => 'kg'],
                ['name' => 'Cebola', 'default_unit' => 'kg'],
                ['name' => 'Couve', 'default_unit' => 'molho'],
            ],
            'Frutas' => [
                ['name' => 'Banana', 'default_unit' => 'cacho'],
                ['name' => 'Manga', 'default_unit' => 'kg'],
            ],
        ];

        foreach ($catalog as $categoryName => $products) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName],
            );

            foreach ($products as $product) {
                $category->products()->firstOrCreate(
                    ['slug' => Str::slug($product['name'])],
                    ['name' => $product['name'], 'default_unit' => $product['default_unit']],
                );
            }
        }
    }
}