<?php

namespace App\Services\Sms;

interface SmsGateway
{
    /**
     * Envia uma mensagem SMS para o número indicado (formato +258XXXXXXXXX).
     */
    public function send(string $phone, string $message): bool;
}