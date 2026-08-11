<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Web — AgroLink MZ
|--------------------------------------------------------------------------
| A definir nas próximas fases (ver docs/ROADMAP.md):
|   Fase 4  — auth/registo/OTP
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
