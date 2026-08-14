<?php

namespace App\Services;

use App\Exceptions\OrderWorkflowException;
use App\Models\Delivery;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeliveryWorkflowService
{
    public function __construct(private readonly NotificationService $notifications) {}

    /** RN21 — estados da entrega, por ordem. */
    private const array TRANSITIONS = [
        'solicitada' => ['atribuida'],
        'atribuida' => ['em_recolha'],
        'em_recolha' => ['em_transito'],
        'em_transito' => ['entregue'],
    ];

    /**
     * RN20 — o operador regista o transportador (formal ou por contacto
     * manual — coordenação assistida do piloto) e o custo acordado, que
     * passa a integrar o valor total do pedido.
     */
    public function assign(
        Delivery $delivery,
        ?int $transporterId,
        ?string $transporterContact,
        float $cost,
        ?\DateTimeInterface $pickupAt,
    ): void {
        $this->guardTransition($delivery, 'atribuida');

        DB::transaction(function () use ($delivery, $transporterId, $transporterContact, $cost, $pickupAt) {
            $delivery->update([
                'transporter_id' => $transporterId,
                'transporter_contact' => $transporterContact,
                'cost' => $cost,
                'pickup_at' => $pickupAt,
                'status' => 'atribuida',
            ]);

            $order = $delivery->order()->getResults();
            $subtotal = $order->items->sum('subtotal');
            $order->update(['delivery_fee' => $cost, 'total_amount' => $subtotal + $cost]);

            $this->notifications->notify(
                $order->buyer,
                'entrega_atribuida',
                "AgroLink MZ: a entrega do pedido #{$order->id} foi atribuída.",
            );
        });
    }

    /** Secção 21 — evento notificado: entrega a caminho. */
    public function advance(Delivery $delivery, string $toStatus): void
    {
        $this->guardTransition($delivery, $toStatus);
        $delivery->update(['status' => $toStatus]);

        if ($toStatus === 'em_transito') {
            $order = $delivery->order()->getResults();
            $this->notifications->notify(
                $order->buyer,
                'entrega_a_caminho',
                "AgroLink MZ: a sua encomenda do pedido #{$order->id} está a caminho.",
            );
        }
    }

    /** RN22 — confirmação de entrega pelo comprador; conclui o pedido associado. */
    public function confirm(Delivery $delivery, User $buyer, OrderWorkflowService $orderWorkflow): void
    {
        if ($delivery->status !== 'entregue') {
            throw new OrderWorkflowException('A entrega ainda não foi marcada como entregue pelo operador.');
        }

        // A relação "order" pode estar em cache na instância desde uma chamada
        // anterior (ex.: assign()); recarrega-se para confirmar o estado actual.
        $order = $delivery->order()->getResults();

        if ($order->status !== 'entregue') {
            throw new OrderWorkflowException('O produtor ainda não marcou o pedido como entregue.');
        }

        DB::transaction(function () use ($delivery, $order, $buyer, $orderWorkflow) {
            $delivery->update(['status' => 'confirmada', 'delivered_at' => now()]);
            $orderWorkflow->advance($order, $buyer, 'concluido');
        });
    }

    private function guardTransition(Delivery $delivery, string $to): void
    {
        if (! in_array($to, self::TRANSITIONS[$delivery->status] ?? [], true)) {
            throw new OrderWorkflowException("Não é possível passar a entrega de \"{$delivery->status}\" para \"{$to}\".");
        }
    }
}