<?php

namespace App\Services;

use App\Exceptions\OrderWorkflowException;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ProductListing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderWorkflowService
{
    /**
     * Quem pode fazer cada transição a partir de que estado.
     * "producer" e "buyer" referem-se ao lado do pedido; a autorização
     * concreta (RN14) é sempre confirmada pela OrderPolicy antes de chamar
     * este serviço — este mapa é só a máquina de estados (RN09).
     */
    private const array TRANSITIONS = [
        'pendente' => ['aceite' => 'producer', 'rejeitado' => 'producer', 'cancelado' => 'buyer'],
        'aceite' => ['em_preparacao' => 'producer', 'cancelado' => 'buyer'],
        'em_preparacao' => ['pronto' => 'producer'],
        'pronto' => ['em_transporte' => 'producer'],
        'em_transporte' => ['entregue' => 'producer'],
        'entregue' => ['concluido' => 'buyer'],
    ];

    /** RN05 — cria o pedido depois de validar a quantidade contra a oferta. */
    public function create(User $buyer, ProductListing $listing, float $quantity, string $deliveryMethod): Order
    {
        if ($listing->status !== 'disponivel' || $listing->available_until->isPast()) {
            throw new OrderWorkflowException('Esta oferta já não está disponível.');
        }

        if ($quantity <= 0 || $quantity > (float) $listing->quantity) {
            throw new OrderWorkflowException('A quantidade pedida excede a quantidade disponível.');
        }

        return DB::transaction(function () use ($buyer, $listing, $quantity, $deliveryMethod) {
            $subtotal = round($quantity * (float) $listing->price, 2);

            $order = Order::create([
                'buyer_id' => $buyer->id,
                'producer_id' => $listing->producer_id,
                'total_amount' => $subtotal,
                'delivery_method' => $deliveryMethod,
                'delivery_fee' => 0,
                'status' => 'pendente',
            ]);

            $order->items()->create([
                'product_listing_id' => $listing->id,
                'quantity' => $quantity,
                'unit_price' => $listing->price,
                'subtotal' => $subtotal,
            ]);

            $this->recordHistory($order, null, 'pendente', $buyer);

            return $order;
        });
    }

    /**
     * RN06/RN15 — reserva atómica: bloqueia a linha da oferta, confirma que
     * ainda há quantidade suficiente e só então decrementa o stock. RN07 — a
     * oferta permanece "disponivel" enquanto sobrar quantidade, e só passa a
     * "vendido" quando esgotar (permite vender a vários compradores).
     */
    public function accept(Order $order, User $producer): void
    {
        $this->guardTransition($order, 'aceite');

        DB::transaction(function () use ($order, $producer) {
            $item = $order->items()->sole();
            $listing = ProductListing::whereKey($item->product_listing_id)->lockForUpdate()->first();

            if (! $listing || $listing->quantity < $item->quantity) {
                throw new OrderWorkflowException('Já não há quantidade suficiente nesta oferta para aceitar o pedido.');
            }

            $remaining = $listing->quantity - $item->quantity;
            $listing->update([
                'quantity' => $remaining,
                'status' => $remaining <= 0 ? 'vendido' : 'disponivel',
            ]);

            $this->transition($order, 'aceite', $producer);
            $this->createDeliveryIfIntermediated($order, $listing);
        });
    }

    /**
     * RN19/RN21 — quando o comprador escolhe transporte intermediado, gera-se
     * o registo de entrega associado ao pedido, pronto a ser atribuído pelo
     * operador (coordenação assistida do piloto — secção 16.2).
     */
    private function createDeliveryIfIntermediated(Order $order, ProductListing $listing): void
    {
        if ($order->delivery_method !== 'transporte_intermediado') {
            return;
        }

        Delivery::create([
            'order_id' => $order->id,
            'origin_lat' => $listing->farm?->latitude,
            'origin_lng' => $listing->farm?->longitude,
            'status' => 'solicitada',
        ]);
    }

    public function reject(Order $order, User $producer): void
    {
        $this->guardTransition($order, 'rejeitado');
        $this->transition($order, 'rejeitado', $producer);
    }

    /** RN11 — livre antes da aceitação; após aceitação, repõe a quantidade reservada. */
    public function cancel(Order $order, User $buyer): void
    {
        $this->guardTransition($order, 'cancelado');

        DB::transaction(function () use ($order, $buyer) {
            if ($order->status === 'aceite') {
                $item = $order->items()->sole();
                $listing = ProductListing::whereKey($item->product_listing_id)->lockForUpdate()->first();

                if ($listing) {
                    $listing->update([
                        'quantity' => $listing->quantity + $item->quantity,
                        'status' => 'disponivel',
                    ]);
                }
            }

            $this->transition($order, 'cancelado', $buyer);
        });
    }

    /** Avança o pedido para o próximo estado do fluxo de preparação/entrega/conclusão. */
    public function advance(Order $order, User $actor, string $toStatus): void
    {
        $this->guardTransition($order, $toStatus);
        $this->transition($order, $toStatus, $actor);
    }

    private function guardTransition(Order $order, string $to): void
    {
        if (! isset(self::TRANSITIONS[$order->status][$to])) {
            throw new OrderWorkflowException("Não é possível passar de \"{$order->status}\" para \"{$to}\".");
        }
    }

    private function transition(Order $order, string $to, User $actor): void
    {
        $from = $order->status;
        $order->update(['status' => $to]);
        $this->recordHistory($order, $from, $to, $actor);

        if ($to === 'concluido') {
            $this->recordTransaction($order);
        }
    }

    /**
     * RN24/RN28 — ao concluir o pedido, regista-se a transação com a
     * comissão da plataforma (percentagem configurável, referência 2%).
     * Nesta fase (piloto, sem escrow integrado) é só o registo contabilístico
     * — a libertação de valores automática fica para a Fase de integração
     * de mobile money (secção 17.3).
     */
    private function recordTransaction(Order $order): void
    {
        $percent = config('commission.percent');
        $amount = (float) $order->total_amount;
        $commission = round($amount * $percent / 100, 2);

        Transaction::updateOrCreate(
            ['order_id' => $order->id],
            [
                'amount' => $amount,
                'commission_percent' => $percent,
                'commission_amount' => $commission,
                'status' => 'concluida',
            ],
        );
    }

    private function recordHistory(Order $order, ?string $from, string $to, User $changedBy): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $changedBy->id,
            'changed_at' => now(),
        ]);
    }
}