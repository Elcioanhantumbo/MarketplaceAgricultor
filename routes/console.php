<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Comandos de consola — AgroLink MZ
|--------------------------------------------------------------------------
| Ex.: expiração de ofertas (RN17), lembretes de OTP, jobs de notificação.
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
