<?php

namespace App\Console\Commands;

use App\Models\ProductListing;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:expire-product-listings')]
#[Description('RN17 — marca como expiradas as ofertas fora do período de disponibilidade.')]
class ExpireProductListings extends Command
{
    public function handle(): void
    {
        $count = ProductListing::whereIn('status', ['disponivel', 'reservado'])
            ->whereDate('available_until', '<', now())
            ->update(['status' => 'expirado']);

        $this->info("{$count} oferta(s) marcada(s) como expirada(s).");
    }
}