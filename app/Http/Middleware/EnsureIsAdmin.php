<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    /** Painel administrativo (secção 9/11.3) — administrador ou operador. */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'operator'], true), 403);

        return $next($request);
    }
}