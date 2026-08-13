<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** RN30 — trilho de auditoria de acções administrativas sensíveis. */
class AuditLogger
{
    public function log(User $actor, string $action, ?Model $subject = null, array $old = [], array $new = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $actor->id,
            'action' => $action,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
        ]);
    }
}