<?php

namespace Tests\Feature\Listings;

use App\Livewire\Listings\Search;
use App\Models\Category;
use App\Models\Location;
use App\Models\Producer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchListingsTest extends TestCase
{
    use RefreshDatabase;

    private function producer(): Producer
    {
        $user = User::factory()->create(['role' => 'producer']);

        return Producer::create(['user_id' => $user->id]);
    }

    private function listing(Producer $producer, array $overrides = []): \App\Models\ProductListing
    {
        $category = Category::firstOrCreate(['slug' => 'cereais'], ['name' => 'Cereais']);
        $product = Product::firstOrCreate(
            ['slug' => 'milho'],
            ['category_id' => $category->id, 'name' => 'Milho', 'default_unit' => 'kg'],
        );

        return $producer->productListings()->create(array_merge([
            'product_id' => $product->id,
            'quantity' => 100,
            'unit' => 'kg',
            'price' => 50,
            'available_from' => now()->subDay(),
            'available_until' => now()->addDays(10),
            'status' => 'disponivel',
        ], $overrides));
    }

    public function test_only_available_non_expired_listings_are_shown(): void
    {
        $producer = $this->producer();
        $visible = $this->listing($producer);
        $sold = $this->listing($producer, ['status' => 'vendido']);
        $expired = $this->listing($producer, ['available_until' => now()->subDay()]);

        $ids = Livewire::test(Search::class)->viewData('listings')->pluck('id');

        $this->assertTrue($ids->contains($visible->id));
        $this->assertFalse($ids->contains($sold->id));
        $this->assertFalse($ids->contains($expired->id));
    }

    public function test_price_range_filter(): void
    {
        $producer = $this->producer();
        $cheap = $this->listing($producer, ['price' => 10]);
        $expensive = $this->listing($producer, ['price' => 500]);

        $ids = Livewire::test(Search::class)
            ->set('max_price', '100')
            ->viewData('listings')
            ->pluck('id');

        $this->assertTrue($ids->contains($cheap->id));
        $this->assertFalse($ids->contains($expensive->id));
    }

    public function test_category_filter(): void
    {
        $producer = $this->producer();
        $milho = $this->listing($producer);

        $frutas = Category::create(['name' => 'Frutas', 'slug' => 'frutas']);
        $banana = $frutas->products()->create(['name' => 'Banana', 'slug' => 'banana', 'default_unit' => 'cacho']);
        $bananaListing = $producer->productListings()->create([
            'product_id' => $banana->id,
            'quantity' => 20,
            'unit' => 'cacho',
            'price' => 100,
            'available_from' => now()->subDay(),
            'available_until' => now()->addDays(10),
            'status' => 'disponivel',
        ]);

        $cereais = Category::where('slug', 'cereais')->first();

        $ids = Livewire::test(Search::class)
            ->set('category_id', $cereais->id)
            ->viewData('listings')
            ->pluck('id');

        $this->assertTrue($ids->contains($milho->id));
        $this->assertFalse($ids->contains($bananaListing->id));
    }

    public function test_nearby_filter_excludes_listings_outside_radius(): void
    {
        $this->seed(\Database\Seeders\LocationSeeder::class);

        $producer = $this->producer();

        // Beira (seeded pilot location): -19.8437, 34.8389
        $near = $this->listing($producer, ['latitude' => -19.8437, 'longitude' => 34.8389]);
        // Maputo, ~500km de distância — fora do raio de 50km.
        $far = $this->listing($producer, ['latitude' => -25.9692, 'longitude' => 32.5732]);

        $beira = Location::where('name', 'Beira')->first();

        $ids = Livewire::test(Search::class)
            ->set('location_id', $beira->id)
            ->set('radius_km', 50)
            ->viewData('listings')
            ->pluck('id');

        $this->assertTrue($ids->contains($near->id));
        $this->assertFalse($ids->contains($far->id));
    }
}