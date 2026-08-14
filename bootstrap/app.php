<?php

use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\EnsurePhoneIsVerified;
use App\Http\Middleware\EnsureTwoFactorConfirmed;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'verified.phone' => EnsurePhoneIsVerified::class,
            'admin' => EnsureIsAdmin::class,
            'admin.2fa' => EnsureTwoFactorConfirmed::class,
        ]);

        // Secção 22 — em produção, a aplicação corre atrás de um
        // proxy/balanceador que termina o HTTPS; sem isto, Laravel vê o
        // pedido como HTTP simples e gera URLs/cookies incorrectos.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
