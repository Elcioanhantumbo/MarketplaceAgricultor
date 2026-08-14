<?php

namespace Tests\Feature\Notifications;

use App\Models\Buyer;
use App\Models\Category;
use App\Models\NotificationLog;
use App\Models\Producer;
use App\Models\ProductListing;
use App\Models\User;
use App\Services\DeliveryWorkflowService;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
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

    public function test_creating_an_order_notifies_the_producer(): void
    {
        $workflow = app(OrderWorkflowService::class);
        $listing = $this->listing();
        $buyer = $this->buyer();

        $order = $workflow->create($buyer, $listing, 4, 'comprador_levanta');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $listing->producer->user_id,
            'event' => 'novo_pedido',
            'status' => 'enviado',
        ]);
        $this->assertStringContainsString((string) $order->id, NotificationLog::where('event', 'novo_pedido')->value('message'));
    }

    public function test_accepting_and_rejecting_notify_the_buyer(): void
    {
        $workflow = app(OrderWorkflowService::class);
        $listing = $this->listing();
        $buyer = $this->buyer();
        $producer = $listing->producer->user;

        $order = $workflow->create($buyer, $listing, 4, 'comprador_levanta');
        $workflow->accept($order, $producer);

        $this->assertDatabaseHas('notifications', ['user_id' => $buyer->id, 'event' => 'pedido_aceite']);

        $order2 = $workflow->create($buyer, $this->listing(), 4, 'comprador_levanta');
        $workflow->reject($order2, $order2->producer->user);

        $this->assertDatabaseHas('notifications', ['user_id' => $buyer->id, 'event' => 'pedido_rejeitado']);
    }

    public function test_concluding_an_order_notifies_both_parties(): void
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

        $this->assertDatabaseHas('notifications', ['user_id' => $buyer->id, 'event' => 'pedido_concluido']);
        $this->assertDatabaseHas('notifications', ['user_id' => $producer->id, 'event' => 'pedido_concluido']);
    }

    public function test_registering_payment_notifies_the_producer(): void
    {
        $workflow = app(OrderWorkflowService::class);
        $paymentService = app(PaymentService::class);
        $listing = $this->listing();
        $buyer = $this->buyer();
        $producer = $listing->producer->user;

        $order = $workflow->create($buyer, $listing, 4, 'comprador_levanta');
        $workflow->accept($order, $producer);
        $paymentService->register($order, 'mpesa', 'REF1');

        $this->assertDatabaseHas('notifications', ['user_id' => $producer->id, 'event' => 'pagamento_recebido']);
    }

    public function test_delivery_assignment_and_transit_notify_the_buyer(): void
    {
        $workflow = app(OrderWorkflowService::class);
        $deliveryWorkflow = app(DeliveryWorkflowService::class);
        $listing = $this->listing();
        $buyer = $this->buyer();
        $producer = $listing->producer->user;

        $order = $workflow->create($buyer, $listing, 4, 'transporte_intermediado');
        $workflow->accept($order, $producer);
        $delivery = $order->fresh()->delivery;

        $deliveryWorkflow->assign($delivery, null, 'João', 50, null);
        $this->assertDatabaseHas('notifications', ['user_id' => $buyer->id, 'event' => 'entrega_atribuida']);

        $deliveryWorkflow->advance($delivery, 'em_recolha');
        $deliveryWorkflow->advance($delivery, 'em_transito');
        $this->assertDatabaseHas('notifications', ['user_id' => $buyer->id, 'event' => 'entrega_a_caminho']);
    }
}