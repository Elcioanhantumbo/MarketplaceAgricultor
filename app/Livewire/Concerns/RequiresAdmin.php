<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Defesa em profundidade para os componentes do painel administrativo: o
 * middleware 'admin' já protege as rotas, mas Livewire::test() instancia os
 * componentes directamente sem passar pelo middleware — este trait garante
 * a mesma verificação directamente no componente.
 */
trait RequiresAdmin
{
    public function mount(): void
    {
        abort_unless(in_array(Auth::user()?->role, ['admin', 'operator'], true), 403);
    }
}