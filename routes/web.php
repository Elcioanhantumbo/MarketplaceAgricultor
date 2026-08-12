<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyOtp;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Web — AgroLink MZ
|--------------------------------------------------------------------------
| A definir nas próximas fases (ver docs/ROADMAP.md):
|   Fase 5  — perfis, categorias, produtos
|   Fase 6  — ofertas, pesquisa
|   Fase 7  — pedidos
|   Fase 8  — entregas
|   Fase 9  — pagamentos
|   Fase 10 — painel administrativo
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/registo', Register::class)->name('registo');
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/verificar-telefone', VerifyOtp::class)->name('verificar-telefone');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::get('/painel', Dashboard::class)
        ->middleware('verified.phone')
        ->name('painel');
});
