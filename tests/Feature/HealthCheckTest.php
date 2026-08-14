<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Secção 22/23 — /up é o alvo recomendado para monitorização externa
 * (UptimeRobot, Healthchecks.io, etc. — ver docs/DEPLOY.md). Sozinho, o
 * health-check de origem do Laravel só confirma que a aplicação arrancou;
 * um listener em AppServiceProvider acrescenta uma verificação real de
 * ligação à base de dados, para que uma falha de DB também dispare alerta.
 */
class HealthCheckTest extends TestCase
{
    public function test_health_check_reports_up_when_database_is_reachable(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_health_check_reports_down_when_database_is_unreachable(): void
    {
        config(['database.connections.pgsql.port' => 1]);
        DB::purge('pgsql');

        $this->get('/up')->assertStatus(500);
    }
}