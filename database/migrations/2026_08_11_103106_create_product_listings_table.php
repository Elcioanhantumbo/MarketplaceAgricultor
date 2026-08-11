<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->constrained('producers')->cascadeOnDelete();
            $table->foreignId('farm_id')->nullable()->constrained('farms')->nullOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->decimal('price', 12, 2);
            $table->date('available_from');
            $table->date('available_until');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('status', ['disponivel', 'reservado', 'vendido', 'expirado'])->default('disponivel');
            $table->timestamps();
        });

        // RN04 — a quantidade disponível nunca pode ser negativa.
        DB::statement('ALTER TABLE product_listings ADD CONSTRAINT product_listings_quantity_non_negative CHECK (quantity >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_listings');
    }
};