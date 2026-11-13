<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public static function log(User $user, string $action, ?Model $model = null, ?array $old = null, ?array $new = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 255),
        ]);
    }
}
