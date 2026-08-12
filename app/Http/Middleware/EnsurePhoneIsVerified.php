<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    /**
     * RN01 — bloqueia o acesso à aplicação enquanto o telefone não estiver verificado.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->phone_verified_at) {
            return redirect()->route('verificar-telefone');
        }

        return $next($request);
    }
}