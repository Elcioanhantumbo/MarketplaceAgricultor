<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Sms\SmsGateway;

/**
 * Secção 21 — SMS e WhatsApp são os canais primários (o público-alvo
 * efectivamente usa-os); o envio real depende do agregador escolhido
 * (SmsGateway::class, ainda "log" — ver Fase 4). Este serviço garante que,
 * independentemente do driver, o evento fica sempre registado em
 * notification_logs para consulta posterior.
 */
class NotificationService
{
    public function __construct(private readonly SmsGateway $sms) {}

    public function notify(User $user, string $event, string $message): NotificationLog
    {
        $log = NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'sms',
            'event' => $event,
            'message' => $message,
            'status' => 'pendente',
        ]);

        $sent = $this->sms->send($user->phone, $message);

        $log->update([
            'status' => $sent ? 'enviado' : 'falhado',
            'sent_at' => $sent ? now() : null,
        ]);

        return $log;
    }
}