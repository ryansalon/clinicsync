<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /** Clinical narrative and secret fields masked so audit logs record what changed without duplicating PHI. */
    private const REDACTED_FIELDS = [
        'chief_complaint',
        'reason',
        'diagnosis',
        'diagnosis_code',
        'assessment',
        'treatment_plan',
        'clinical_notes',
        'instructions',
        'reaction',
        'notes',
        'password',
        'remember_token',
    ];

    public static function log(User $user, string $action, ?Model $model = null, ?array $old = null, ?array $new = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'old_values' => self::redact($old),
            'new_values' => self::redact($new),
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 255),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $values
     * @return array<string, mixed>|null
     */
    private static function redact(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        foreach (self::REDACTED_FIELDS as $field) {
            if (array_key_exists($field, $values) && $values[$field] !== null && $values[$field] !== '') {
                $values[$field] = '[redacted]';
            }
        }

        return $values;
    }
}
