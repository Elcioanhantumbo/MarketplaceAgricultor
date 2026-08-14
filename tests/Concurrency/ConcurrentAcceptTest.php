<?php

namespace Tests\Concurrency;

use App\Models\Buyer;
use App\Models\Category;
use App\Models\Producer;
use App\Models\ProductListing;
use App\Models\User;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * RN06/RN15 — a reserva de stock tem de resistir a dois pedidos aceites ao
 * mesmo tempo, não só em sequência. Os restantes testes (ex.:
 * OrderWorkflowServiceTest::test_accept_fails_if_stock_was_consumed_by_another_order_meanwhile)
 * simulam a concorrência chamando accept() duas vezes seguidas no mesmo
 * processo/transacção — o que prova a lógica de negócio, mas não o
 * lockForUpdate em si, porque o RefreshDatabase embrulha cada teste numa
 * transacção só visível a essa ligação.
 *
 * Este teste usa DatabaseMigrations (sem embrulhar em transacção) e dois
 * processos reais do SO, cada um com a sua própria ligação à base de dados,
 * sincronizados por uma barreira de ficheiro para maximizar a sobreposição
 * exactamente no lockForUpdate.
 */
class ConcurrentAcceptTest extends TestCase
{
    use DatabaseMigrations;

    public function test_only_one_of_two_concurrent_accepts_succeeds_when_stock_is_insufficient_for_both(): void
    {
        $producerUser = User::factory()->create(['role' => 'producer']);
        $producer = Producer::create(['user_id' => $producerUser->id]);
        $category = Category::create(['name' => 'Cereais', 'slug' => 'cereais']);
        $product = $category->products()->create(['name' => 'Milho', 'slug' => 'milho', 'default_unit' => 'kg']);

        $listing = $producer->productListings()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
            'price' => 10,
            'available_from' => now()->subDay(),
            'available_until' => now()->addDays(10),
            'status' => 'disponivel',
        ]);

        $workflow = app(OrderWorkflowService::class);

        $buyerA = User::factory()->create(['role' => 'buyer']);
        Buyer::create(['user_id' => $buyerA->id]);
        $orderA = $workflow->create($buyerA, $listing->fresh(), 6, 'comprador_levanta');

        $buyerB = User::factory()->create(['role' => 'buyer']);
        Buyer::create(['user_id' => $buyerB->id]);
        $orderB = $workflow->create($buyerB, $listing->fresh(), 6, 'comprador_levanta');

        $startFile = sys_get_temp_dir().'/agrolink_concurrency_'.uniqid().'.start';
        $script = __DIR__.'/support/accept_order_process.php';

        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $processA = proc_open([PHP_BINARY, $script, (string) $orderA->id, (string) $producerUser->id, $startFile], $descriptors, $pipesA, base_path());
        $processB = proc_open([PHP_BINARY, $script, (string) $orderB->id, (string) $producerUser->id, $startFile], $descriptors, $pipesB, base_path());

        $this->assertIsResource($processA);
        $this->assertIsResource($processB);

        // Dá tempo aos dois processos para arrancarem o Laravel e ficarem à
        // espera da barreira antes de a abrir — é isto que maximiza a
        // sobreposição real no lockForUpdate.
        usleep(300_000);
        file_put_contents($startFile, '1');

        $outputA = stream_get_contents($pipesA[1]);
        $errorA = stream_get_contents($pipesA[2]);
        fclose($pipesA[1]);
        fclose($pipesA[2]);
        $exitA = proc_close($processA);

        $outputB = stream_get_contents($pipesB[1]);
        $errorB = stream_get_contents($pipesB[2]);
        fclose($pipesB[1]);
        fclose($pipesB[2]);
        $exitB = proc_close($processB);

        @unlink($startFile);

        $this->assertSame(0, $exitA, "Processo A falhou a arrancar: {$errorA}");
        $this->assertSame(0, $exitB, "Processo B falhou a arrancar: {$errorB}");

        $results = [$outputA, $outputB];
        $successes = array_filter($results, fn ($r) => $r === 'OK');
        $failures = array_filter($results, fn ($r) => str_starts_with($r, 'FAIL:'));

        $this->assertCount(1, $successes, 'Exactamente um dos dois accepts concorrentes devia ter sucesso: '.implode(' | ', $results));
        $this->assertCount(1, $failures, 'O outro accept devia falhar por falta de stock: '.implode(' | ', $results));

        $listing->refresh();
        $this->assertSame('4.00', (string) $listing->quantity, 'O stock nunca pode ficar negativo nem ser vendido a mais (RN06/RN15).');

        $acceptedCount = \App\Models\Order::whereIn('id', [$orderA->id, $orderB->id])->where('status', 'aceite')->count();
        $this->assertSame(1, $acceptedCount, 'Só um dos dois pedidos concorrentes pode ficar aceite.');
    }
}