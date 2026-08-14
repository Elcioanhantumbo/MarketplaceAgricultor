<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorConfirmed
{
    /**
     * Secção 22 — 2FA para contas administrativas: exige um segundo código
     * (OTP por SMS, reaproveitando o OtpService da secção 4/RN01) confirmado
     * nesta sessão antes de aceder a qualquer página do painel /admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('two_factor_confirmed_at')) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('confirmar-acesso');
        }

        return $next($request);
    }
}