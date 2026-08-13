<?php

namespace Tests\Feature\Services;

use App\Exceptions\OrderWorkflowException;
use App\Models\Buyer;
use App\Models\Category;
use App\Models\Producer;
use App\Models\ProductListing;
use App\Models\User;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderWorkflowService $workflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflow = app(OrderWorkflowService::class);
    }

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

    public function test_create_rejects_quantity_above_available(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();

        $this->expectException(OrderWorkflowException::class);
        $this->workflow->create($buyer, $listing, 20, 'comprador_levanta');
    }

    public function test_create_computes_total_from_quantity_and_price(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();

        $order = $this->workflow->create($buyer, $listing, 4, 'comprador_levanta');

        $this->assertSame('40.00', (string) $order->total_amount);
        $this->assertSame('pendente', $order->status);
        $this->assertSame(1, $order->statusHistory()->count());
    }

    public function test_accept_reserves_quantity_and_keeps_listing_available_when_stock_remains(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();
        $order = $this->workflow->create($buyer, $listing, 4, 'comprador_levanta');

        $this->workflow->accept($order, $listing->producer->user);

        $listing->refresh();
        $this->assertSame('6.00', (string) $listing->quantity);
        $this->assertSame('disponivel', $listing->status);
        $this->assertSame('aceite', $order->fresh()->status);
    }

    public function test_accept_marks_listing_as_sold_when_stock_reaches_zero(): void
    {
        $listing = $this->listing(quantity: 5);
        $buyer = $this->buyer();
        $order = $this->workflow->create($buyer, $listing, 5, 'comprador_levanta');

        $this->workflow->accept($order, $listing->producer->user);

        $this->assertSame('0.00', (string) $listing->fresh()->quantity);
        $this->assertSame('vendido', $listing->fresh()->status);
    }

    public function test_accept_fails_if_stock_was_consumed_by_another_order_meanwhile(): void
    {
        // RN15 — simula concorrência: duas encomendas pendentes cuja soma
        // excede o stock; a primeira aceitação esgota-o, a segunda deve falhar.
        $listing = $this->listing(quantity: 10);
        $buyerA = $this->buyer();
        $buyerB = $this->buyer();

        $orderA = $this->workflow->create($buyerA, $listing, 6, 'comprador_levanta');
        $orderB = $this->workflow->create($buyerB, $listing, 6, 'comprador_levanta');

        $this->workflow->accept($orderA, $listing->producer->user);

        $this->expectException(OrderWorkflowException::class);
        $this->workflow->accept($orderB, $listing->producer->user);
    }

    public function test_cannot_accept_a_non_pending_order(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();
        $order = $this->workflow->create($buyer, $listing, 4, 'comprador_levanta');
        $this->workflow->accept($order, $listing->producer->user);

        $this->expectException(OrderWorkflowException::class);
        $this->workflow->accept($order, $listing->producer->user);
    }

    public function test_reject_moves_pending_order_to_rejected(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();
        $order = $this->workflow->create($buyer, $listing, 4, 'comprador_levanta');

        $this->workflow->reject($order, $listing->producer->user);

        $this->assertSame('rejeitado', $order->fresh()->status);
        $this->assertSame('10.00', $listing->fresh()->quantity, 'Rejeitar não deve alterar o stock — nunca foi reservado.');
    }

    public function test_cancel_before_acceptance_is_free_and_does_not_touch_stock(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();
        $order = $this->workflow->create($buyer, $listing, 4, 'comprador_levanta');

        $this->workflow->cancel($order, $buyer);

        $this->assertSame('cancelado', $order->fresh()->status);
        $this->assertSame('10.00', (string) $listing->fresh()->quantity);
    }

    public function test_cancel_after_acceptance_restores_reserved_quantity(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();
        $order = $this->workflow->create($buyer, $listing, 4, 'comprador_levanta');
        $this->workflow->accept($order, $listing->producer->user);

        $this->workflow->cancel($order, $buyer);

        $this->assertSame('cancelado', $order->fresh()->status);
        $this->assertSame('10.00', (string) $listing->fresh()->quantity);
        $this->assertSame('disponivel', $listing->fresh()->status);
    }

    public function test_full_happy_path_transitions_and_history(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();
        $producer = $listing->producer->user;
        $order = $this->workflow->create($buyer, $listing, 4, 'comprador_levanta');

        $this->workflow->accept($order, $producer);
        $this->workflow->advance($order, $producer, 'em_preparacao');
        $this->workflow->advance($order, $producer, 'pronto');
        $this->workflow->advance($order, $producer, 'em_transporte');
        $this->workflow->advance($order, $producer, 'entregue');
        $this->workflow->advance($order, $buyer, 'concluido');

        $this->assertSame('concluido', $order->fresh()->status);
        $this->assertSame(
            ['pendente', 'aceite', 'em_preparacao', 'pronto', 'em_transporte', 'entregue', 'concluido'],
            $order->statusHistory()->orderBy('id')->pluck('to_status')->all(),
        );
    }

    public function test_cannot_skip_states(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();
        $order = $this->workflow->create($buyer, $listing, 4, 'comprador_levanta');

        $this->expectException(OrderWorkflowException::class);
        $this->workflow->advance($order, $buyer, 'concluido');
    }

    public function test_cannot_cancel_once_preparation_has_started(): void
    {
        $listing = $this->listing(quantity: 10);
        $buyer = $this->buyer();
        $producer = $listing->producer->user;
        $order = $this->workflow->create($buyer, $listing, 4, 'comprador_levanta');
        $this->workflow->accept($order, $producer);
        $this->workflow->advance($order, $producer, 'em_preparacao');

        $this->expectException(OrderWorkflowException::class);
        $this->workflow->cancel($order, $buyer);
    }
}