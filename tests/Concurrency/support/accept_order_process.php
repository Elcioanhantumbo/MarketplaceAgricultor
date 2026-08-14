<?php

/**
 * Processo isolado usado por ConcurrentAcceptTest (RN06/RN15). Corre num
 * processo do SO à parte (via proc_open), com a sua própria ligação à base
 * de dados — só assim se testa o lockForUpdate real, e não uma simulação
 * sequencial dentro do mesmo processo/transacção do PHPUnit.
 *
 * Argumentos: <orderId> <producerUserId> <ficheiroDeArranque>
 * Espera pelo ficheiro de arranque (barreira) antes de chamar accept(), para
 * maximizar a sobreposição real dos dois processos no lockForUpdate.
 * Imprime "OK" ou "FAIL:<mensagem>" para stdout.
 */

require __DIR__.'/../../../vendor/autoload.php';

[$script, $orderId, $producerUserId, $startFile] = $argv;

$app = require __DIR__.'/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deadline = microtime(true) + 5;
while (! file_exists($startFile)) {
    if (microtime(true) > $deadline) {
        fwrite(STDERR, "Timeout à espera da barreira de arranque.\n");
        exit(2);
    }
    usleep(1000);
}

$workflow = $app->make(App\Services\OrderWorkflowService::class);
$order = App\Models\Order::findOrFail((int) $orderId);
$producer = App\Models\User::findOrFail((int) $producerUserId);

try {
    $workflow->accept($order, $producer);
    echo 'OK';
} catch (App\Exceptions\OrderWorkflowException $e) {
    echo 'FAIL:'.$e->getMessage();
}