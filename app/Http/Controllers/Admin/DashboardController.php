<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Physician;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * System administrator control center: live clinic pulse, 7-day volume, readiness checks and audit feed.
     */
    public function index(): View
    {
        $today = now()->toDateString();
        $weekStart = now()->subDays(6)->toDateString();

        $todayQueue = Appointment::query()->forDate($today)->get(['status', 'source']);
        $weekAppointments = Appointment::query()
            ->where('appointment_date', '>=', $weekStart)
            ->where('appointment_date', '<=', $today)
            ->get(['appointment_date', 'status', 'source']);

        $byDate = $weekAppointments->groupBy(fn (Appointment $a) => $a->appointment_date->toDateString());
        $trends = collect(range(6, 0))->map(function (int $daysAgo) use ($byDate) {
            $date = now()->subDays($daysAgo);
            $key = $date->toDateString();
            $rows = $byDate->get($key, collect());

            return [
                'label' => $date->isoFormat('ddd'),
                'dateLabel' => $date->isoFormat('MMM D'),
                'total' => $rows->count(),
                'completed' => $rows->where('status', Appointment::STATUS_COMPLETED)->count(),
                'noShow' => $rows->where('status', Appointment::STATUS_NO_SHOW)->count(),
                'walkIns' => $rows->where('source', 'walk_in')->count(),
            ];
        });

        $unscheduledPhysicians = Physician::query()
            ->whereDoesntHave('activeSchedules')
            ->with('user')
            ->get();

        return view('admin.dashboard', [
            'todayTotal' => $todayQueue->count(),
            'todayWaiting' => $todayQueue->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])->count(),
            'todayCompleted' => $todayQueue->where('status', Appointment::STATUS_COMPLETED)->count(),
            'todayEncounters' => Encounter::query()->whereDate('encountered_at', $today)->count(),
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'suspendedUsers' => User::query()->where('is_active', false)->count(),
            'totalPatients' => Patient::query()->count(),
            'totalPhysicians' => Physician::query()->count(),
            'unscheduledPhysicians' => $unscheduledPhysicians,
            'trends' => $trends,
            'maxTrendTotal' => max(1, (int) $trends->max('total')),
            'recentLogs' => AuditLog::query()->with('user')->latest('id')->limit(6)->get(),
        ]);
    }
}
