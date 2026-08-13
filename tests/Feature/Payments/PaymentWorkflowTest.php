<?php

namespace Tests\Feature\Payments;

use App\Exceptions\OrderWorkflowException;
use App\Livewire\Orders\Show as ShowOrder;
use App\Models\Buyer;
use App\Models\Category;
use App\Models\Order;
use App\Models\Producer;
use App\Models\ProductListing;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private OrderWorkflowService $orderWorkflow;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderWorkflow = app(OrderWorkflowService::class);
        $this->paymentService = app(PaymentService::class);
    }

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

    private function pendingOrder(): Order
    {
        return $this->orderWorkflow->create($this->buyer(), $this->listing(), 4, 'comprador_levanta');
    }

    private function acceptedOrder(): Order
    {
        $order = $this->pendingOrder();
        $this->orderWorkflow->accept($order, $order->producer->user);

        return $order->fresh();
    }

    public function test_cannot_register_payment_before_order_is_accepted(): void
    {
        $order = $this->pendingOrder();

        $this->expectException(OrderWorkflowException::class);
        $this->paymentService->register($order, 'mpesa', 'REF123');
    }

    public function test_registering_payment_twice_for_the_same_order_updates_instead_of_duplicating(): void
    {
        $order = $this->acceptedOrder();

        $this->paymentService->register($order, 'mpesa', 'REF123');
        $this->paymentService->register($order, 'mpesa', 'REF123');

        $this->assertSame(1, $order->payments()->count());
    }

    public function test_reusing_a_provider_reference_on_another_order_is_rejected(): void
    {
        $orderA = $this->acceptedOrder();
        $orderB = $this->acceptedOrder();

        $this->paymentService->register($orderA, 'mpesa', 'REF-DUPLICADA');

        $this->expectException(OrderWorkflowException::class);
        $this->paymentService->register($orderB, 'mpesa', 'REF-DUPLICADA');
    }

    public function test_payment_amount_is_taken_from_the_order_not_client_input(): void
    {
        $order = $this->acceptedOrder();

        $payment = $this->paymentService->register($order, 'dinheiro', null);

        $this->assertSame((string) $order->total_amount, (string) $payment->amount);
        $this->assertSame('pago', $payment->status);
    }

    public function test_buyer_and_producer_can_register_payment_via_the_order_page(): void
    {
        $order = $this->acceptedOrder();

        Livewire::actingAs($order->buyer)
            ->test(ShowOrder::class, ['order' => $order])
            ->set('payment_method', 'emola')
            ->set('payment_reference', 'ABC')
            ->call('registerPayment')
            ->assertHasNoErrors();

        $this->assertSame(1, $order->payments()->count());
    }

    public function test_unrelated_user_cannot_register_payment(): void
    {
        $order = $this->acceptedOrder();
        $stranger = $this->buyer();

        Livewire::actingAs($stranger)
            ->test(ShowOrder::class, ['order' => $order])
            ->assertForbidden();
    }

    public function test_concluding_an_order_records_a_transaction_with_configured_commission(): void
    {
        config(['commission.percent' => 5]);

        $order = $this->acceptedOrder();
        $buyer = $order->buyer;
        $producer = $order->producer->user;

        $this->orderWorkflow->advance($order, $producer, 'em_preparacao');
        $this->orderWorkflow->advance($order, $producer, 'pronto');
        $this->orderWorkflow->advance($order, $producer, 'em_transporte');
        $this->orderWorkflow->advance($order, $producer, 'entregue');
        $this->orderWorkflow->advance($order, $buyer, 'concluido');

        $transaction = Transaction::where('order_id', $order->id)->first();
        $this->assertNotNull($transaction);
        $this->assertSame('5.00', (string) $transaction->commission_percent);
        $this->assertSame(number_format((float) $order->total_amount * 0.05, 2, '.', ''), (string) $transaction->commission_amount);
        $this->assertSame('concluida', $transaction->status);
    }
}