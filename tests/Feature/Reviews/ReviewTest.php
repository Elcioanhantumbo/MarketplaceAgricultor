<?php

namespace Tests\Feature\Reviews;

use App\Livewire\Orders\Show as ShowOrder;
use App\Models\Buyer;
use App\Models\Category;
use App\Models\Order;
use App\Models\Producer;
use App\Models\ProductListing;
use App\Models\Review;
use App\Models\User;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function listing(float $quantity = 100): ProductListing
    {
        $producerUser = User::factory()->create(['role' => 'producer']);
        $producer = Producer::create(['user_id' => $producerUser->id]);
        $category = Category::firstOrCreate(['slug' => 'cereais'], ['name' => 'Cereais']);
        $product = $category->products()->firstOrCreate(['slug' => 'milho'], ['name' => 'Milho', 'default_unit' => 'kg']);

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

    private function concludedOrder(): Order
    {
        $workflow = app(OrderWorkflowService::class);
        $listing = $this->listing();
        $buyer = $this->buyer();
        $producer = $listing->producer->user;

        $order = $workflow->create($buyer, $listing, 4, 'comprador_levanta');
        $workflow->accept($order, $producer);
        $workflow->advance($order, $producer, 'em_preparacao');
        $workflow->advance($order, $producer, 'pronto');
        $workflow->advance($order, $producer, 'em_transporte');
        $workflow->advance($order, $producer, 'entregue');
        $workflow->advance($order, $buyer, 'concluido');

        return $order->fresh();
    }

    public function test_buyer_can_review_producer_after_conclusion(): void
    {
        $order = $this->concludedOrder();

        Livewire::actingAs($order->buyer)
            ->test(ShowOrder::class, ['order' => $order])
            ->set('review_rating', 5)
            ->set('review_comment', 'Excelente produtor.')
            ->call('submitReview')
            ->assertHasNoErrors();

        $review = Review::where('order_id', $order->id)->first();
        $this->assertNotNull($review);
        $this->assertSame($order->buyer_id, $review->reviewer_id);
        $this->assertSame($order->producer->user_id, $review->reviewee_id);
        $this->assertSame(5, $review->rating);
    }

    public function test_producer_can_review_buyer_after_conclusion(): void
    {
        $order = $this->concludedOrder();
        $producer = $order->producer->user;

        Livewire::actingAs($producer)
            ->test(ShowOrder::class, ['order' => $order])
            ->set('review_rating', 4)
            ->call('submitReview')
            ->assertHasNoErrors();

        $review = Review::where('order_id', $order->id)->first();
        $this->assertSame($producer->id, $review->reviewer_id);
        $this->assertSame($order->buyer_id, $review->reviewee_id);
    }

    public function test_cannot_review_before_order_is_concluded(): void
    {
        $workflow = app(OrderWorkflowService::class);
        $listing = $this->listing();
        $buyer = $this->buyer();
        $order = $workflow->create($buyer, $listing, 4, 'comprador_levanta');

        Livewire::actingAs($buyer)
            ->test(ShowOrder::class, ['order' => $order])
            ->set('review_rating', 5)
            ->call('submitReview')
            ->assertForbidden();

        $this->assertSame(0, Review::count());
    }

    public function test_cannot_review_the_same_order_twice(): void
    {
        $order = $this->concludedOrder();

        Livewire::actingAs($order->buyer)
            ->test(ShowOrder::class, ['order' => $order])
            ->set('review_rating', 5)
            ->call('submitReview');

        Livewire::actingAs($order->buyer)
            ->test(ShowOrder::class, ['order' => $order])
            ->set('review_rating', 3)
            ->call('submitReview')
            ->assertHasErrors('review_comment');

        $this->assertSame(1, Review::where('order_id', $order->id)->count());
    }

    public function test_unrelated_user_cannot_even_open_the_order_to_review_it(): void
    {
        // OrderPolicy::view() já bloqueia no mount() — um estranho nem chega
        // a carregar a página do pedido, muito menos a submeter uma avaliação.
        $order = $this->concludedOrder();
        $stranger = $this->buyer();

        Livewire::actingAs($stranger)
            ->test(ShowOrder::class, ['order' => $order])
            ->assertForbidden();

        $this->assertSame(0, Review::where('reviewer_id', $stranger->id)->count());
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $order = $this->concludedOrder();

        Livewire::actingAs($order->buyer)
            ->test(ShowOrder::class, ['order' => $order])
            ->set('review_rating', 9)
            ->call('submitReview')
            ->assertHasErrors('review_rating');
    }
}