<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Physician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Daily census: booking mix, queue outcomes and per-physician load.
     */
    public function index(Request $request): View
    {
        $date = (string) $request->query('date', now()->toDateString());

        /** The census only changes when appointments or encounters are written, so a
            short-lived cache absorbs repeat loads and date flipping. */
        $stats = Cache::remember(
            "reports:daily:{$date}",
            now()->addMinute(),
            fn () => $this->dailyCensus($date),
        );

        return view('admin.reports.index', $stats + ['date' => $date]);
    }

    /**
     * Aggregate one day of appointments and encounters into report rows.
     *
     * @return array{appointments: Collection<int, Appointment>, encounters: Collection<int, Encounter>, byPhysician: Collection, byStatus: Collection, online: int, walkIns: int, registeredPatients: int, activeAccounts: int, physicianCount: int}
     */
    private function dailyCensus(string $date): array
    {
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();
        $nextDayStart = Carbon::parse($date)->startOfDay()->addDay();

        $appointments = Appointment::query()
            ->forDate($date)
            ->with(['patient.user', 'physician.user'])
            ->get();

        $encounters = Encounter::query()
            ->whereBetween('encountered_at', [$dayStart, $dayEnd])
            ->with(['patient.user', 'physician.user'])
            ->get();

        $byPhysician = $appointments
            ->groupBy(fn (Appointment $appointment) => $appointment->physician_id)
            ->map(function (Collection $group) {
                return [
                    'physician' => $group->first()->physician,
                    'total' => $group->count(),
                    'pending' => $group->where('status', Appointment::STATUS_PENDING)->count(),
                    'confirmed' => $group->where('status', Appointment::STATUS_CONFIRMED)->count(),
                    'completed' => $group->where('status', Appointment::STATUS_COMPLETED)->count(),
                    'cancelled' => $group->where('status', Appointment::STATUS_CANCELLED)->count(),
                    'no_show' => $group->where('status', Appointment::STATUS_NO_SHOW)->count(),
                ];
            })
            ->values();

        return [
            'appointments' => $appointments,
            'encounters' => $encounters,
            'byPhysician' => $byPhysician,
            'byStatus' => collect(Appointment::STATUSES)
                ->mapWithKeys(fn (string $status) => [$status => $appointments->where('status', $status)->count()]),
            'online' => $appointments->where('source', 'online')->count(),
            'walkIns' => $appointments->where('source', 'walk_in')->count(),
            'registeredPatients' => Patient::query()->where('created_at', '<', $nextDayStart)->count(),
            'activeAccounts' => User::query()->where('is_active', true)->count(),
            'physicianCount' => Physician::query()->count(),
        ];
    }
}
