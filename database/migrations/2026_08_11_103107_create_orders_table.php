<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('producer_id')->constrained('producers')->restrictOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('delivery_method', ['comprador_levanta', 'produtor_entrega', 'transporte_intermediado']);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->enum('status', [
                'pendente', 'aceite', 'em_preparacao', 'pronto', 'em_transporte',
                'entregue', 'concluido', 'rejeitado', 'cancelado',
            ])->default('pendente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};