<?php

use App\Console\Commands\BackupDatabase;
use App\Console\Commands\ExpireProductListings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Comandos de consola — AgroLink MZ
|--------------------------------------------------------------------------
| Ex.: expiração de ofertas (RN17), lembretes de OTP, jobs de notificação.
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ExpireProductListings::class)->daily();
Schedule::command(BackupDatabase::class)->dailyAt('02:00');
