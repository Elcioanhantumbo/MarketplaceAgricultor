<?php

namespace App\Providers;

use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\SmsGateway;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsGateway::class, function () {
            return match (config('services.sms.driver')) {
                // Activar aqui quando o agregador de SMS local for contratado (secção 21).
                default => new LogSmsGateway,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Secção 22 — HTTPS em produção, mesmo que o pedido chegue por um
        // proxy/balanceador que só fala HTTP para a aplicação.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Secção 22/23 — /up (monitorização externa) só responde bem se a
        // base de dados também estiver acessível, não só a aplicação em si.
        Event::listen(DiagnosingHealth::class, function (): void {
            try {
                DB::connection()->getPdo();
            } catch (\Throwable $e) {
                throw new RuntimeException('Base de dados inacessível: '.$e->getMessage(), previous: $e);
            }
        });
    }
}
