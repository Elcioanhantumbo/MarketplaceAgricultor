<?php

namespace App\Services;

use App\Exceptions\OrderWorkflowException;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private readonly NotificationService $notifications) {}

    private const array METHODS = ['mpesa', 'emola', 'mkesh', 'transferencia', 'dinheiro'];

    /**
     * RN25 — fase piloto (secção 17.2): o pagamento é combinado directamente
     * entre comprador e produtor e apenas registado na plataforma, com o
     * método e a referência indicados. RN26 — repetir o registo do mesmo
     * pedido actualiza o registo existente em vez de duplicar (idempotente).
     * RN18 — o valor é sempre recalculado no servidor a partir do pedido.
     */
    public function register(Order $order, string $method, ?string $providerReference): Payment
    {
        if (in_array($order->status, ['pendente', 'rejeitado', 'cancelado'], true)) {
            throw new OrderWorkflowException('Só é possível registar o pagamento depois de o pedido ser aceite.');
        }

        if (! in_array($method, self::METHODS, true)) {
            throw new OrderWorkflowException('Método de pagamento inválido.');
        }

        try {
            return DB::transaction(function () use ($order, $method, $providerReference) {
                $payment = Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'method' => $method,
                        'amount' => $order->total_amount,
                        'provider_reference' => $providerReference,
                        'status' => 'pago',
                    ],
                );

                $this->notifications->notify(
                    $order->producer->user,
                    'pagamento_recebido',
                    "AgroLink MZ: pagamento registado para o pedido #{$order->id}.",
                );

                return $payment;
            });
        } catch (UniqueConstraintViolationException) {
            throw new OrderWorkflowException('Esta referência de pagamento já está associada a outro pedido.');
        }
    }
}