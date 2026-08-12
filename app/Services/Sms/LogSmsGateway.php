<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Driver por omissão enquanto o agregador de SMS local (secção 21) não está
 * contratado: regista a mensagem no log em vez de a enviar.
 */
class LogSmsGateway implements SmsGateway
{
    public function send(string $phone, string $message): bool
    {
        Log::info('[SMS simulado] '.$phone.': '.$message);

        return true;
    }
}