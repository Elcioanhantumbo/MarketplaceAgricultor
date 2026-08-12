<?php

namespace Tests\Feature\Listings;

use App\Livewire\Listings\Manage;
use App\Models\Buyer;
use App\Models\Category;
use App\Models\Producer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageListingsTest extends TestCase
{
    use RefreshDatabase;

    private function readyProducer(): Producer
    {
        $user = User::factory()->create(['role' => 'producer']);
        $producer = Producer::create(['user_id' => $user->id]);
        $user->profile()->create(['address' => 'Bairro Central']);
        $producer->farms()->create(['name' => 'Machamba Teste', 'latitude' => -19.61, 'longitude' => 34.74]);

        return $producer;
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Cereais', 'slug' => 'cereais']);

        return $category->products()->create(['name' => 'Milho', 'slug' => 'milho', 'default_unit' => 'kg']);
    }

    public function test_producer_without_rn02_minimum_cannot_create_listings(): void
    {
        $user = User::factory()->create(['role' => 'producer']);
        Producer::create(['user_id' => $user->id]);

        Livewire::actingAs($user)->test(Manage::class)->assertForbidden();
    }

    public function test_ready_producer_can_publish_a_listing(): void
    {
        $producer = $this->readyProducer();
        $product = $this->product();

        Livewire::actingAs($producer->user)
            ->test(Manage::class)
            ->set('product_id', $product->id)
            ->set('quantity', '100')
            ->set('unit', 'kg')
            ->set('price', '50')
            ->set('available_from', now()->toDateString())
            ->set('available_until', now()->addDays(10)->toDateString())
            ->call('save');

        $this->assertSame(1, $producer->productListings()->count());
        $this->assertSame('disponivel', $producer->productListings()->first()->status);
    }

    public function test_negative_quantity_is_rejected_by_validation(): void
    {
        $producer = $this->readyProducer();
        $product = $this->product();

        Livewire::actingAs($producer->user)
            ->test(Manage::class)
            ->set('product_id', $product->id)
            ->set('quantity', '-5')
            ->set('unit', 'kg')
            ->set('price', '50')
            ->set('available_from', now()->toDateString())
            ->set('available_until', now()->addDays(10)->toDateString())
            ->call('save')
            ->assertHasErrors('quantity');

        $this->assertSame(0, $producer->productListings()->count());
    }

    public function test_producer_cannot_edit_another_producers_listing(): void
    {
        $owner = $this->readyProducer();
        $product = $this->product();
        $listing = $owner->productListings()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
            'price' => 20,
            'available_from' => now(),
            'available_until' => now()->addDays(5),
            'status' => 'disponivel',
        ]);

        $intruder = $this->readyProducer();

        Livewire::actingAs($intruder->user)
            ->test(Manage::class)
            ->call('edit', $listing->id)
            ->assertForbidden();
    }

    public function test_closing_a_listing_marks_it_as_expired(): void
    {
        $producer = $this->readyProducer();
        $product = $this->product();
        $listing = $producer->productListings()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
            'price' => 20,
            'available_from' => now(),
            'available_until' => now()->addDays(5),
            'status' => 'disponivel',
        ]);

        Livewire::actingAs($producer->user)
            ->test(Manage::class)
            ->call('close', $listing->id);

        $this->assertSame('expirado', $listing->fresh()->status);
    }

    public function test_buyer_cannot_access_listing_management(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        Buyer::create(['user_id' => $user->id]);

        Livewire::actingAs($user)->test(Manage::class)->assertForbidden();
    }
}