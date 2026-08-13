<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /** RN14 — só o comprador ou o produtor envolvidos veem o pedido. */
    public function view(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id || $order->producer->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'buyer';
    }

    /** Aceitar/rejeitar/avançar estados de preparação e entrega — acções do produtor. */
    public function manage(User $user, Order $order): bool
    {
        return $order->producer->user_id === $user->id;
    }

    /** RN11 — cancelamento é iniciado pelo comprador. */
    public function cancel(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id;
    }

    /** RN22 — confirmação de entrega pelo comprador. */
    public function confirmDelivery(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id;
    }

    /**
     * RN14/17.2 — o pagamento do piloto é combinado directamente entre as
     * partes; tanto o comprador como o produtor podem registá-lo.
     */
    public function managePayment(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id || $order->producer->user_id === $user->id;
    }
}