<?php

use App\Livewire\Admin\Categories as AdminCategories;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Disputes as AdminDisputes;
use App\Livewire\Admin\Listings as AdminListings;
use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\Users as AdminUsers;
use App\Livewire\Admin\Verifications as AdminVerifications;
use App\Livewire\Auth\ConfirmTwoFactor;
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
use App\Livewire\Notifications\Inbox as NotificationsInbox;
use App\Livewire\Orders\BuyerIndex as MyOrders;
use App\Livewire\Orders\ProducerIndex as ReceivedOrders;
use App\Livewire\Orders\Show as ShowOrder;
use App\Livewire\Producers\Show as ShowProducer;
use App\Livewire\Profile\Edit as EditProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Web — AgroLink MZ
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/categorias', CatalogBrowse::class)->name('categorias');
Route::get('/ofertas', SearchListings::class)->name('ofertas');
Route::get('/ofertas/{listing}', ShowListing::class)->name('ofertas.show');
Route::get('/produtores/{producer}', ShowProducer::class)->name('produtores.show');

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
        Route::get('/notificacoes', NotificationsInbox::class)->name('notificacoes');
        Route::get('/confirmar-acesso', ConfirmTwoFactor::class)->name('confirmar-acesso');

        Route::middleware(['admin', 'admin.2fa'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/', AdminDashboard::class)->name('dashboard');
            Route::get('/utilizadores', AdminUsers::class)->name('utilizadores');
            Route::get('/verificacoes', AdminVerifications::class)->name('verificacoes');
            Route::get('/categorias', AdminCategories::class)->name('categorias');
            Route::get('/ofertas', AdminListings::class)->name('ofertas');
            Route::get('/pedidos', AdminOrders::class)->name('pedidos');
            Route::get('/disputas', AdminDisputes::class)->name('disputas');
        });
    });
});
