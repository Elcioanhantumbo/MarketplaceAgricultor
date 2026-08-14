<?php

namespace Tests\Feature\Producers;

use App\Livewire\Producers\Show as ShowProducer;
use App\Models\Buyer;
use App\Models\Category;
use App\Models\Producer;
use App\Models\ProductListing;
use App\Models\User;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProducerProfileTest extends TestCase
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

    public function test_public_profile_shows_no_rating_without_reviews(): void
    {
        $listing = $this->listing();

        Livewire::test(ShowProducer::class, ['producer' => $listing->producer])
            ->assertOk()
            ->assertSee('Ainda sem avaliações')
            ->assertSee($listing->product->name);
    }

    public function test_public_profile_shows_average_rating_after_reviews(): void
    {
        $workflow = app(OrderWorkflowService::class);
        $listing = $this->listing();
        $producer = $listing->producer;

        foreach ([5, 3] as $rating) {
            $buyer = $this->buyer();
            $order = $workflow->create($buyer, $listing->fresh(), 1, 'comprador_levanta');
            $workflow->accept($order, $producer->user);
            $workflow->advance($order, $producer->user, 'em_preparacao');
            $workflow->advance($order, $producer->user, 'pronto');
            $workflow->advance($order, $producer->user, 'em_transporte');
            $workflow->advance($order, $producer->user, 'entregue');
            $workflow->advance($order, $buyer, 'concluido');

            $order->reviews()->create([
                'reviewer_id' => $buyer->id,
                'reviewee_id' => $producer->user_id,
                'rating' => $rating,
            ]);
        }

        $this->assertSame(4.0, $producer->averageRating());
        $this->assertSame(2, $producer->reviewsCount());

        Livewire::test(ShowProducer::class, ['producer' => $producer])
            ->assertSee('4.0')
            ->assertSee('2 avaliações');
    }

    public function test_public_profile_only_lists_currently_available_listings(): void
    {
        $listing = $this->listing();
        $listing->update(['status' => 'vendido']);

        Livewire::test(ShowProducer::class, ['producer' => $listing->producer])
            ->assertDontSee($listing->product->name);
    }
}