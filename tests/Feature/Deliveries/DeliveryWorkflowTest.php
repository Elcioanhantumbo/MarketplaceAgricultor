<?php

namespace Tests\Feature\Deliveries;

use App\Exceptions\OrderWorkflowException;
use App\Livewire\Deliveries\Manage;
use App\Models\Buyer;
use App\Models\Category;
use App\Models\Order;
use App\Models\Producer;
use App\Models\ProductListing;
use App\Models\User;
use App\Services\DeliveryWorkflowService;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeliveryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private OrderWorkflowService $orderWorkflow;

    private DeliveryWorkflowService $deliveryWorkflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderWorkflow = app(OrderWorkflowService::class);
        $this->deliveryWorkflow = app(DeliveryWorkflowService::class);
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

    private function acceptedOrder(string $deliveryMethod): Order
    {
        $listing = $this->listing();
        $buyer = $this->buyer();
        $order = $this->orderWorkflow->create($buyer, $listing, 4, $deliveryMethod);
        $this->orderWorkflow->accept($order, $listing->producer->user);

        return $order->fresh();
    }

    public function test_accepting_an_intermediated_order_creates_a_delivery(): void
    {
        $order = $this->acceptedOrder('transporte_intermediado');

        $this->assertNotNull($order->delivery);
        $this->assertSame('solicitada', $order->delivery->status);
    }

    public function test_accepting_a_self_pickup_order_does_not_create_a_delivery(): void
    {
        $order = $this->acceptedOrder('comprador_levanta');

        $this->assertNull($order->fresh()->delivery);
    }

    public function test_assign_updates_delivery_and_recalculates_order_total(): void
    {
        $order = $this->acceptedOrder('transporte_intermediado');
        $originalTotal = (float) $order->total_amount;

        $this->deliveryWorkflow->assign($order->delivery, null, 'João, 84 000 0000', 50, null);

        $order->refresh();
        $this->assertSame('atribuida', $order->delivery->fresh()->status);
        $this->assertSame('50.00', (string) $order->delivery_fee);
        $this->assertEqualsWithDelta($originalTotal + 50, (float) $order->total_amount, 0.001);
    }

    public function test_delivery_progresses_through_states_in_order(): void
    {
        $order = $this->acceptedOrder('transporte_intermediado');
        $delivery = $order->delivery;
        $this->deliveryWorkflow->assign($delivery, null, 'João', 50, null);

        $this->deliveryWorkflow->advance($delivery, 'em_recolha');
        $this->deliveryWorkflow->advance($delivery, 'em_transito');
        $this->deliveryWorkflow->advance($delivery, 'entregue');

        $this->assertSame('entregue', $delivery->fresh()->status);
    }

    public function test_cannot_skip_delivery_states(): void
    {
        $order = $this->acceptedOrder('transporte_intermediado');
        $delivery = $order->delivery;
        $this->deliveryWorkflow->assign($delivery, null, 'João', 50, null);

        $this->expectException(OrderWorkflowException::class);
        $this->deliveryWorkflow->advance($delivery, 'entregue');
    }

    public function test_confirm_requires_delivery_and_order_both_marked_as_delivered(): void
    {
        $order = $this->acceptedOrder('transporte_intermediado');
        $delivery = $order->delivery;
        $this->deliveryWorkflow->assign($delivery, null, 'João', 50, null);
        $this->deliveryWorkflow->advance($delivery, 'em_recolha');
        $this->deliveryWorkflow->advance($delivery, 'em_transito');
        $this->deliveryWorkflow->advance($delivery, 'entregue');

        // A entrega já está "entregue" mas o produtor ainda não avançou o pedido.
        $this->expectException(OrderWorkflowException::class);
        $this->deliveryWorkflow->confirm($delivery, $order->buyer, $this->orderWorkflow);
    }

    public function test_confirm_completes_delivery_and_concludes_order(): void
    {
        $order = $this->acceptedOrder('transporte_intermediado');
        $producer = $order->producer->user;
        $delivery = $order->delivery;

        $this->deliveryWorkflow->assign($delivery, null, 'João', 50, null);
        $this->deliveryWorkflow->advance($delivery, 'em_recolha');
        $this->deliveryWorkflow->advance($delivery, 'em_transito');
        $this->deliveryWorkflow->advance($delivery, 'entregue');

        $this->orderWorkflow->advance($order, $producer, 'em_preparacao');
        $this->orderWorkflow->advance($order, $producer, 'pronto');
        $this->orderWorkflow->advance($order, $producer, 'em_transporte');
        $this->orderWorkflow->advance($order, $producer, 'entregue');

        $this->deliveryWorkflow->confirm($delivery, $order->buyer, $this->orderWorkflow);

        $this->assertSame('confirmada', $delivery->fresh()->status);
        $this->assertNotNull($delivery->fresh()->delivered_at);
        $this->assertSame('concluido', $order->fresh()->status);
    }

    public function test_only_operator_or_admin_can_access_delivery_management(): void
    {
        $buyer = $this->buyer();

        Livewire::actingAs($buyer)->test(Manage::class)->assertForbidden();

        $operator = User::factory()->create(['role' => 'operator']);
        Livewire::actingAs($operator)->test(Manage::class)->assertOk();
    }

    public function test_operator_can_assign_a_delivery_through_the_ui(): void
    {
        $order = $this->acceptedOrder('transporte_intermediado');
        $operator = User::factory()->create(['role' => 'operator']);

        Livewire::actingAs($operator)
            ->test(Manage::class)
            ->call('startAssigning', $order->delivery->id)
            ->set('transporter_contact', 'Maria, 82 111 2222')
            ->set('cost', '75')
            ->call('assign');

        $this->assertSame('atribuida', $order->delivery->fresh()->status);
        $this->assertSame('75.00', (string) $order->fresh()->delivery_fee);
    }
}