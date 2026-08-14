<?php

namespace Tests\Feature\EndToEnd;

use App\Livewire\Deliveries\Manage as ManageDeliveries;
use App\Livewire\Farms\Manage as ManageFarms;
use App\Livewire\Listings\Manage as ManageListings;
use App\Livewire\Listings\Show as ShowListing;
use App\Livewire\Notifications\Inbox;
use App\Livewire\Orders\Show as ShowOrder;
use App\Livewire\Producers\Show as ShowProducer;
use App\Livewire\Profile\Edit as EditProfile;
use App\Models\Buyer;
use App\Models\Category;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Fase 13 — percorre o ciclo de vida inteiro (produtor → comprador → entrega
 * → pagamento → conclusão → avaliação) sempre através dos componentes
 * Livewire que um utilizador real usaria, em vez de chamar os serviços
 * directamente como fazem os testes unitários de cada módulo. O objectivo
 * não é repetir a cobertura já feita módulo a módulo, mas apanhar problemas
 * de "ligação" entre fases que só aparecem ao encadeá-las (ex.: dados que
 * uma fase espera e a anterior não deixou prontos).
 */
class FullOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private function producerReadyToPublish(): User
    {
        $user = User::factory()->create(['role' => 'producer']);
        Producer::create(['user_id' => $user->id]);

        Livewire::actingAs($user)->test(EditProfile::class)
            ->set('name', 'Produtor Piloto')
            ->set('business_name', 'Machambas Piloto Lda')
            ->set('address', 'Estrada Nacional 6, Nhamatanda')
            ->set('district', 'Nhamatanda')
            ->set('province', 'Sofala')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($user)->test(ManageFarms::class)
            ->set('name', 'Machamba Piloto')
            ->set('address', 'Km 12, Nhamatanda')
            ->set('district', 'Nhamatanda')
            ->set('province', 'Sofala')
            ->set('latitude', '-19.15')
            ->set('longitude', '34.45')
            ->call('save')
            ->assertHasNoErrors();

        return $user->fresh();
    }

    private function buyerWithProfile(): User
    {
        $user = User::factory()->create(['role' => 'buyer']);
        Buyer::create(['user_id' => $user->id]);

        Livewire::actingAs($user)->test(EditProfile::class)
            ->set('name', 'Comprador Piloto')
            ->set('business_name', 'Restaurante Piloto')
            ->set('buyer_type', 'restaurante')
            ->set('address', 'Avenida Poder Popular, Beira')
            ->call('save')
            ->assertHasNoErrors();

        return $user->fresh();
    }

    private function product(): Product
    {
        $category = Category::firstOrCreate(['slug' => 'cereais'], ['name' => 'Cereais']);

        return $category->products()->firstOrCreate(['slug' => 'milho'], ['name' => 'Milho', 'default_unit' => 'kg']);
    }

    private function publishListing(User $producerUser, float $quantity = 100): ProductListing
    {
        $farmId = $producerUser->producer->farms()->sole()->id;

        Livewire::actingAs($producerUser)->test(ManageListings::class)
            ->set('farm_id', $farmId)
            ->set('product_id', $this->product()->id)
            ->set('quantity', (string) $quantity)
            ->set('unit', 'kg')
            ->set('price', '15')
            ->set('available_from', now()->subDay()->toDateString())
            ->set('available_until', now()->addDays(15)->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        return ProductListing::where('producer_id', $producerUser->producer->id)->latest()->firstOrFail();
    }

    public function test_full_flow_from_publishing_to_review_with_self_pickup(): void
    {
        $producerUser = $this->producerReadyToPublish();
        $buyerUser = $this->buyerWithProfile();
        $listing = $this->publishListing($producerUser, quantity: 50);

        // Comprador encomenda a partir da página pública da oferta.
        $orderComponent = Livewire::actingAs($buyerUser)->test(ShowListing::class, ['listing' => $listing])
            ->set('quantity', '20')
            ->set('delivery_method', 'comprador_levanta')
            ->call('order');
        $orderComponent->assertHasNoErrors()->assertRedirect();

        $order = Order::where('buyer_id', $buyerUser->id)->sole();
        $this->assertSame('pendente', $order->status);
        $this->assertSame('300.00', (string) $order->total_amount);

        // Produtor recebeu notificação do novo pedido.
        $this->assertDatabaseHas('notifications', [
            'user_id' => $producerUser->id,
            'event' => 'novo_pedido',
        ]);

        // Produtor aceita — reserva de stock (RN06) e sem entrega (levantamento próprio).
        Livewire::actingAs($producerUser)->test(ShowOrder::class, ['order' => $order])
            ->call('accept')
            ->assertHasNoErrors();

        $this->assertSame('aceite', $order->fresh()->status);
        $this->assertSame('30.00', (string) $listing->fresh()->quantity);
        $this->assertNull($order->fresh()->delivery);

        // Produtor avança o pedido até "entregue".
        $show = Livewire::actingAs($producerUser)->test(ShowOrder::class, ['order' => $order]);
        foreach (['em_preparacao', 'pronto', 'em_transporte', 'entregue'] as $status) {
            $show->call('advance', $status)->assertHasNoErrors();
        }
        $this->assertSame('entregue', $order->fresh()->status);

        // Pagamento (piloto): comprador regista o pagamento combinado directamente.
        Livewire::actingAs($buyerUser)->test(ShowOrder::class, ['order' => $order])
            ->set('payment_method', 'mpesa')
            ->set('payment_reference', 'MPESA-E2E-1')
            ->call('registerPayment')
            ->assertHasNoErrors();

        $this->assertSame(1, $order->payments()->count());

        // Comprador confirma a recepção — sem registo de entrega, isto conclui o pedido directamente.
        Livewire::actingAs($buyerUser)->test(ShowOrder::class, ['order' => $order])
            ->call('confirmDelivery')
            ->assertHasNoErrors();

        $order->refresh();
        $this->assertSame('concluido', $order->status);

        // RN24/RN28 — comissão registada automaticamente ao concluir.
        $transaction = Transaction::where('order_id', $order->id)->sole();
        $this->assertSame('concluida', $transaction->status);
        $this->assertEqualsWithDelta((float) $order->total_amount * (config('commission.percent') / 100), (float) $transaction->commission_amount, 0.01);

        // Ambas as partes foram notificadas da conclusão.
        $this->assertDatabaseHas('notifications', ['user_id' => $buyerUser->id, 'event' => 'pedido_concluido']);
        $this->assertDatabaseHas('notifications', ['user_id' => $producerUser->id, 'event' => 'pedido_concluido']);

        // RN13 — avaliação mútua depois da conclusão.
        Livewire::actingAs($buyerUser)->test(ShowOrder::class, ['order' => $order])
            ->set('review_rating', 5)
            ->set('review_comment', 'Milho de óptima qualidade, tudo como combinado.')
            ->call('submitReview')
            ->assertHasNoErrors();

        Livewire::actingAs($producerUser)->test(ShowOrder::class, ['order' => $order])
            ->set('review_rating', 4)
            ->call('submitReview')
            ->assertHasNoErrors();

        $this->assertSame(2, Review::where('order_id', $order->id)->count());

        // A reputação do produtor fica visível no seu perfil público (RN13/secção 11.2).
        Livewire::test(ShowProducer::class, ['producer' => $producerUser->producer])
            ->assertSee('5.0')
            ->assertSee('1 avaliação');

        // O comprador vê a notificação de conclusão na sua caixa de entrada.
        Livewire::actingAs($buyerUser)->test(Inbox::class)
            ->assertSee('concluído');
    }

    public function test_full_flow_with_intermediated_delivery(): void
    {
        $producerUser = $this->producerReadyToPublish();
        $buyerUser = $this->buyerWithProfile();
        $operator = User::factory()->create(['role' => 'operator']);
        $listing = $this->publishListing($producerUser, quantity: 40);

        Livewire::actingAs($buyerUser)->test(ShowListing::class, ['listing' => $listing])
            ->set('quantity', '10')
            ->set('delivery_method', 'transporte_intermediado')
            ->call('order')
            ->assertHasNoErrors();

        $order = Order::where('buyer_id', $buyerUser->id)->sole();

        Livewire::actingAs($producerUser)->test(ShowOrder::class, ['order' => $order])
            ->call('accept')
            ->assertHasNoErrors();

        // RN19/RN21 — pedido com transporte intermediado gera automaticamente a entrega.
        $order->refresh();
        $this->assertNotNull($order->delivery);
        $this->assertSame('solicitada', $order->delivery->status);

        // Operador atribui o transportador e o custo (RN20 — passa a integrar o total).
        $originalTotal = (float) $order->total_amount;
        Livewire::actingAs($operator)->test(ManageDeliveries::class)
            ->call('startAssigning', $order->delivery->id)
            ->set('transporter_contact', 'Carlos, 84 555 1234')
            ->set('cost', '60')
            ->call('assign')
            ->assertHasNoErrors();

        $order->refresh();
        $this->assertSame('atribuida', $order->delivery->fresh()->status);
        $this->assertEqualsWithDelta($originalTotal + 60, (float) $order->total_amount, 0.01);
        $this->assertDatabaseHas('notifications', ['user_id' => $buyerUser->id, 'event' => 'entrega_atribuida']);

        // Operador avança a entrega até "em_transito".
        $manage = Livewire::actingAs($operator)->test(ManageDeliveries::class);
        $manage->call('advance', $order->delivery->id, 'em_recolha')->assertHasNoErrors();
        $manage->call('advance', $order->delivery->id, 'em_transito')->assertHasNoErrors();
        $this->assertDatabaseHas('notifications', ['user_id' => $buyerUser->id, 'event' => 'entrega_a_caminho']);

        $manage->call('advance', $order->delivery->id, 'entregue')->assertHasNoErrors();
        $this->assertSame('entregue', $order->delivery->fresh()->status);

        // Produtor avança o pedido em paralelo até "entregue" (RN22 exige ambos).
        $show = Livewire::actingAs($producerUser)->test(ShowOrder::class, ['order' => $order]);
        foreach (['em_preparacao', 'pronto', 'em_transporte', 'entregue'] as $status) {
            $show->call('advance', $status)->assertHasNoErrors();
        }

        Livewire::actingAs($buyerUser)->test(ShowOrder::class, ['order' => $order])
            ->set('payment_method', 'emola')
            ->call('registerPayment')
            ->assertHasNoErrors();

        // Confirmação do comprador conclui tanto a entrega como o pedido (RN22).
        Livewire::actingAs($buyerUser)->test(ShowOrder::class, ['order' => $order])
            ->call('confirmDelivery')
            ->assertHasNoErrors();

        $order->refresh();
        $this->assertSame('confirmada', $order->delivery->fresh()->status);
        $this->assertSame('concluido', $order->status);
        $this->assertNotNull(Transaction::where('order_id', $order->id)->first());
    }
}