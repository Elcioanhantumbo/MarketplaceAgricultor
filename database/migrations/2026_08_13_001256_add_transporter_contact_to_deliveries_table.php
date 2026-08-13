<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Piloto — coordenação assistida (secção 16.2): o operador regista o
     * contacto do transportador negociado por telefone, mesmo sem conta
     * formal na plataforma.
     */
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('transporter_contact')->nullable()->after('transporter_id');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('transporter_contact');
        });
    }
};