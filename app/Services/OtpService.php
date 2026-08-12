<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\Sms\SmsGateway;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public function __construct(private readonly SmsGateway $sms) {}

    /**
     * Gera e envia um novo código OTP, invalidando quaisquer códigos
     * pendentes anteriores para o mesmo utilizador/finalidade (RN01).
     */
    public function generate(User $user, string $purpose = 'phone_verification'): OtpCode
    {
        $user->otpCodes()
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $code = (string) random_int(0, 10 ** config('otp.code_length') - 1);
        $code = str_pad($code, config('otp.code_length'), '0', STR_PAD_LEFT);

        $otp = $user->otpCodes()->create([
            'purpose' => $purpose,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(config('otp.expiry_minutes')),
        ]);

        $this->sms->send(
            $user->phone,
            "AgroLink MZ: o seu código de verificação é {$code}. Válido por ".config('otp.expiry_minutes').' minutos.',
        );

        return $otp;
    }

    /**
     * Verifica o código introduzido contra o OTP pendente mais recente.
     * Limita tentativas para dificultar ataques de força bruta.
     */
    public function verify(User $user, string $code, string $purpose = 'phone_verification'): bool
    {
        $otp = $user->otpCodes()
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if (! $otp || $otp->isExpired() || $otp->attempts >= config('otp.max_attempts')) {
            return false;
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code)) {
            return false;
        }

        $otp->update(['consumed_at' => now()]);

        if ($purpose === 'phone_verification') {
            // phone_verified_at é definido internamente e não é mass-assignable
            // de propósito, por isso é atribuído directamente em vez de update().
            $user->forceFill(['phone_verified_at' => now(), 'status' => 'active'])->save();
        }

        return true;
    }
}