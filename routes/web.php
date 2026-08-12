<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyOtp;
use App\Livewire\Catalog\Browse as CatalogBrowse;
use App\Livewire\Dashboard;
use App\Livewire\Farms\Manage as ManageFarms;
use App\Livewire\Profile\Edit as EditProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Web — AgroLink MZ
|--------------------------------------------------------------------------
| A definir nas próximas fases (ver docs/ROADMAP.md):
|   Fase 6  — ofertas, pesquisa
|   Fase 7  — pedidos
|   Fase 8  — entregas
|   Fase 9  — pagamentos
|   Fase 10 — painel administrativo
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/categorias', CatalogBrowse::class)->name('categorias');

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

    Route::middleware('verified.phone')->group(function () {
        Route::get('/painel', Dashboard::class)->name('painel');
        Route::get('/perfil', EditProfile::class)->name('perfil');
        Route::get('/minhas-propriedades', ManageFarms::class)->name('minhas-propriedades');
    });
});
