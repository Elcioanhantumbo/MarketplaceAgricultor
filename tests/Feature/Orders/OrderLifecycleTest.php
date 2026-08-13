<?php

namespace Tests\Feature\Orders;

use App\Livewire\Listings\Show as ShowListing;
use App\Livewire\Orders\Show as ShowOrder;
use App\Models\Buyer;
use App\Models\Category;
use App\Models\Order;
use App\Models\Producer;
use App\Models\ProductListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function listing(float $quantity = 100): ProductListing
    {
        $producerUser = User::factory()->create(['role' => 'producer']);
        $producer = Producer::create(['user_id' => $producerUser->id]);
        $category = Category::create(['name' => 'Cereais', 'slug' => 'cereais']);
        $product = $category->products()->create(['name' => 'Milho', 'slug' => 'milho', 'default_unit' => 'kg']);

        return $producer->productListings()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit' => 'kg',
            'price' => 10,
            'available_from' => now()->subDay(),
            'available_until' => now()->addDays(10),
            'status' => 'disponivel',
        ]);
    }

    private function buyer(): User
    {
        $user = User::factory()->create(['role' => 'buyer']);
        Buyer::create(['user_id' => $user->id]);

        return $user;
    }

    public function test_buyer_can_place_an_order_from_the_listing_page(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();

        Livewire::actingAs($buyer)
            ->test(ShowListing::class, ['listing' => $listing])
            ->set('quantity', '3')
            ->set('delivery_method', 'comprador_levanta')
            ->call('order');

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame($buyer->id, $order->buyer_id);
        $this->assertSame('pendente', $order->status);
    }

    public function test_order_form_rejects_quantity_above_available(): void
    {
        $listing = $this->listing(quantity: 5);
        $buyer = $this->buyer();

        Livewire::actingAs($buyer)
            ->test(ShowListing::class, ['listing' => $listing])
            ->set('quantity', '50')
            ->set('delivery_method', 'comprador_levanta')
            ->call('order')
            ->assertHasErrors('quantity');

        $this->assertSame(0, Order::count());
    }

    public function test_producer_can_view_and_accept_own_order(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();
        $producer = $listing->producer->user;

        Livewire::actingAs($buyer)
            ->test(ShowListing::class, ['listing' => $listing])
            ->set('quantity', '3')
            ->set('delivery_method', 'comprador_levanta')
            ->call('order');

        $order = Order::first();

        Livewire::actingAs($producer)
            ->test(ShowOrder::class, ['order' => $order])
            ->call('accept')
            ->assertHasNoErrors();

        $this->assertSame('aceite', $order->fresh()->status);
    }

    public function test_unrelated_user_cannot_view_someone_elses_order(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();

        Livewire::actingAs($buyer)
            ->test(ShowListing::class, ['listing' => $listing])
            ->set('quantity', '3')
            ->set('delivery_method', 'comprador_levanta')
            ->call('order');

        $order = Order::first();
        $stranger = $this->buyer();

        Livewire::actingAs($stranger)
            ->test(ShowOrder::class, ['order' => $order])
            ->assertForbidden();
    }

    public function test_buyer_cannot_accept_their_own_order(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();

        Livewire::actingAs($buyer)
            ->test(ShowListing::class, ['listing' => $listing])
            ->set('quantity', '3')
            ->set('delivery_method', 'comprador_levanta')
            ->call('order');

        $order = Order::first();

        Livewire::actingAs($buyer)
            ->test(ShowOrder::class, ['order' => $order])
            ->call('accept')
            ->assertForbidden();
    }
}