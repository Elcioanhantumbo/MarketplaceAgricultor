<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['mpesa', 'emola', 'mkesh', 'transferencia', 'dinheiro']);
            $table->decimal('amount', 12, 2);
            $table->string('provider_reference')->nullable();
            $table->enum('status', [
                'pendente', 'pago', 'retido', 'libertado', 'reembolsado', 'falhado',
            ])->default('pendente');
            $table->timestamp('held_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            // RN26 — callbacks/retentativas do agregador não podem duplicar pagamentos.
            $table->unique('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};