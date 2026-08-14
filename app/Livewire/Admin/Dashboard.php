<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\RequiresAdmin;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\ProductListing;
use App\Models\Transaction;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Painel administrativo — AgroLink MZ')]
class Dashboard extends Component
{
    use RequiresAdmin;

    /** KPIs do negócio (secção 23). */
    public function render()
    {
        $totalOrders = Order::count();
        $concludedOrders = Order::where('status', 'concluido')->count();

        $totalDeliveries = Delivery::count();
        $confirmedDeliveries = Delivery::where('status', 'confirmada')->count();

        $buyersWithConcludedOrders = Order::where('status', 'concluido')
            ->select('buyer_id')
            ->groupBy('buyer_id')
            ->selectRaw('count(*) as total')
            ->get();

        $kpis = [
            'ofertas_publicadas' => ProductListing::count(),
            'taxa_conclusao' => $totalOrders > 0 ? round($concludedOrders / $totalOrders * 100, 1) : null,
            'taxa_entrega_sucesso' => $totalDeliveries > 0 ? round($confirmedDeliveries / $totalDeliveries * 100, 1) : null,
            'gmv' => Transaction::where('status', 'concluida')->sum('amount'),
            'taxa_recompra' => $buyersWithConcludedOrders->count() > 0
                ? round($buyersWithConcludedOrders->where('total', '>', 1)->count() / $buyersWithConcludedOrders->count() * 100, 1)
                : null,
        ];

        return view('livewire.admin.dashboard', ['kpis' => $kpis]);
    }
}