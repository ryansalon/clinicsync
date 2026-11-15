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
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Daily census: booking mix, queue outcomes and per-physician load.
     */
    public function index(Request $request): View
    {
        $date = $this->resolveDate($request->query('date'));

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
     * Stream the selected day's appointments as a spreadsheet-safe CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $date = $this->resolveDate($request->query('date'));
        $stats = $this->dailyCensus($date);

        return response()->streamDownload(function () use ($stats): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Queue #', 'Patient', 'Patient ID', 'Physician', 'Time', 'Source', 'Status', 'Chief Complaint']);

            foreach ($stats['appointments'] as $appointment) {
                fputcsv($handle, [
                    $appointment->queue_number,
                    $this->csvSafe($appointment->patient?->user?->name ?? 'Unknown'),
                    $this->csvSafe($appointment->patient?->patient_number ?? ''),
                    $this->csvSafe('Dr. '.($appointment->physician?->user?->name ?? 'Unknown')),
                    $appointment->start_time,
                    $appointment->source,
                    $appointment->status,
                    $this->csvSafe($appointment->chief_complaint),
                ]);
            }

            fclose($handle);
        }, "clinicsync-census-{$date}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function resolveDate(mixed $rawDate): string
    {
        return is_string($rawDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate) === 1
            ? rescue(fn () => Carbon::createFromFormat('Y-m-d', $rawDate)->toDateString(), now()->toDateString(), false)
            : now()->toDateString();
    }

    private function csvSafe(?string $value): string
    {
        $string = (string) ($value ?? '');

        return preg_match('/^[=+\-@\t\r]/', $string) === 1 ? "'".$string : $string;
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
