<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    /** Coordenação assistida do piloto (secção 16.2) — operador/administrador. */
    public function manage(User $user): bool
    {
        return in_array($user->role, ['operator', 'admin'], true);
    }

    /** RN14 — comprador e produtor do pedido também podem consultar a entrega. */
    public function view(User $user, Delivery $delivery): bool
    {
        return $this->manage($user)
            || $delivery->order->buyer_id === $user->id
            || $delivery->order->producer->user_id === $user->id;
    }
}