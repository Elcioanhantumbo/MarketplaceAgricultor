<?php

namespace App\Support;

class MozambiquePhone
{
    /**
     * Normaliza um número moçambicano para o formato +258XXXXXXXXX
     * (9 dígitos, a começar por 8[2-7] — RN01). Devolve null se inválido.
     */
    public static function normalize(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone);
        $digits = preg_replace('/^258/', '', $digits);

        if (! preg_match('/^8[2-7][0-9]{7}$/', $digits)) {
            return null;
        }

        return '+258'.$digits;
    }
}