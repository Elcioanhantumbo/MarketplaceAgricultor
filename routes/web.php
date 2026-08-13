<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyOtp;
use App\Livewire\Catalog\Browse as CatalogBrowse;
use App\Livewire\Dashboard;
use App\Livewire\Deliveries\Manage as ManageDeliveries;
use App\Livewire\Farms\Manage as ManageFarms;
use App\Livewire\Listings\Manage as ManageListings;
use App\Livewire\Listings\Search as SearchListings;
use App\Livewire\Listings\Show as ShowListing;
use App\Livewire\Orders\BuyerIndex as MyOrders;
use App\Livewire\Orders\ProducerIndex as ReceivedOrders;
use App\Livewire\Orders\Show as ShowOrder;
use App\Livewire\Profile\Edit as EditProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Web — AgroLink MZ
|--------------------------------------------------------------------------
| A definir nas próximas fases (ver docs/ROADMAP.md):
|   Fase 9  — pagamentos
|   Fase 10 — painel administrativo
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/categorias', CatalogBrowse::class)->name('categorias');
Route::get('/ofertas', SearchListings::class)->name('ofertas');
Route::get('/ofertas/{listing}', ShowListing::class)->name('ofertas.show');

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
        Route::get('/minhas-ofertas', ManageListings::class)->name('minhas-ofertas');
        Route::get('/meus-pedidos', MyOrders::class)->name('meus-pedidos');
        Route::get('/pedidos-recebidos', ReceivedOrders::class)->name('pedidos-recebidos');
        Route::get('/pedidos/{order}', ShowOrder::class)->name('pedidos.show');
        Route::get('/entregas', ManageDeliveries::class)->name('entregas');
    });
});
