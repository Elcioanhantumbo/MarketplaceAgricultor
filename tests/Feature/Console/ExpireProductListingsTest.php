<?php

namespace Tests\Feature\Console;

use App\Models\Category;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireProductListingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_past_due_listings_are_marked_as_expired(): void
    {
        $user = User::factory()->create(['role' => 'producer']);
        $producer = Producer::create(['user_id' => $user->id]);
        $category = Category::create(['name' => 'Cereais', 'slug' => 'cereais']);
        $product = $category->products()->create(['name' => 'Milho', 'slug' => 'milho', 'default_unit' => 'kg']);

        $expired = $producer->productListings()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
            'price' => 20,
            'available_from' => now()->subDays(10),
            'available_until' => now()->subDay(),
            'status' => 'disponivel',
        ]);

        $stillValid = $producer->productListings()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
            'price' => 20,
            'available_from' => now()->subDay(),
            'available_until' => now()->addDays(5),
            'status' => 'disponivel',
        ]);

        $this->artisan('app:expire-product-listings')->assertSuccessful();

        $this->assertSame('expirado', $expired->fresh()->status);
        $this->assertSame('disponivel', $stillValid->fresh()->status);
    }
}