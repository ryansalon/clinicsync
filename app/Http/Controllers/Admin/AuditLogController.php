<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Immutable trail of privileged actions for compliance review.
     */
    public function index(Request $request): View
    {
        $action = trim((string) $request->query('action', ''));
        $userId = $request->query('user_id');

        $logs = AuditLog::query()
            ->with('user')
            ->when($action !== '', fn ($query) => $query->where('action', 'like', "%{$action}%"))
            ->when($userId !== null && $userId !== '', fn ($query) => $query->where('user_id', (int) $userId))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $actorIds = AuditLog::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->limit(200)
            ->pluck('user_id');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'action' => $action,
            'userId' => $userId,
            'users' => User::query()
                ->whereIn('id', $actorIds)
                ->when($userId !== null && $userId !== '', fn ($query) => $query->orWhere('id', (int) $userId))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
