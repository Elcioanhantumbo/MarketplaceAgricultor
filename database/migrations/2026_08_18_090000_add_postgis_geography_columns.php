<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Secção 18.1/6 do business plan — pesquisa geográfica com PostGIS. Ficou
 * adiada nas Fases 2/6 por não haver, na altura, build do PostGIS para a
 * versão do PostgreSQL instalada (ver docs/ROADMAP.md); a fórmula de
 * Haversine sobre latitude/longitude simples serviu de substituto. Esta
 * migration activa o PostGIS agora que há build disponível, e acrescenta
 * colunas geography(Point,4326) para pesquisa por proximidade real
 * (ST_DWithin/ST_Distance), sem remover latitude/longitude — continuam a
 * ser a fonte de verdade para exibição e edição; a coluna geography é
 * mantida em sincronia a partir delas (ver App\Models\Concerns\HasGeoLocation).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');

        foreach (['farms', 'product_listings', 'locations'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->geography('geo_location', subtype: 'point', srid: 4326)->nullable();
            });

            DB::statement("CREATE INDEX {$table}_geo_location_gist ON {$table} USING GIST (geo_location)");

            DB::statement(<<<SQL
                UPDATE {$table}
                SET geo_location = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography
                WHERE latitude IS NOT NULL AND longitude IS NOT NULL
            SQL);
        }
    }

    public function down(): void
    {
        foreach (['farms', 'product_listings', 'locations'] as $table) {
            DB::statement("DROP INDEX IF EXISTS {$table}_geo_location_gist");
            Schema::table($table, fn (Blueprint $table) => $table->dropColumn('geo_location'));
        }

        // A extensão fica instalada mesmo com rollback — outras bases de
        // dados/tabelas podem depender dela; remover é uma decisão manual.
    }
};