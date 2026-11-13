<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Patient home: upcoming visits, latest encounter and record summary.
     */
    public function index(Request $request): View|JsonResponse
    {
        $patient = $request->user()->patient;

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('patient.partials.queue-status', [
                    'patient' => $patient,
                    'queueStatus' => $patient === null ? null : $this->queueStatusFor($patient),
                ])->render(),
            ]);
        }

        $upcoming = $patient === null
            ? collect()
            : Appointment::query()
                ->forPatient($patient->id)
                ->active()
                ->where('appointment_date', '>=', now()->toDateString())
                ->with('physician.user')
                ->orderBy('appointment_date')
                ->orderBy('start_time')
                ->get();

        return view('patient.dashboard', [
            'patient' => $patient,
            'upcoming' => $upcoming,
            'queueStatus' => $patient === null ? null : $this->queueStatusFor($patient),
            'appointmentCount' => $patient === null ? 0 : $patient->appointments()->count(),
            'encounterCount' => $patient === null ? 0 : $patient->encounters()->completed()->count(),
            'prescriptionCount' => $patient === null ? 0 : $patient->prescriptions()->count(),
            'activeAllergies' => $patient === null ? collect() : $patient->activeAllergies()->get(),
            'latestEncounter' => $patient === null
                ? null
                : $patient->encounters()->with('physician.user')->latest('encountered_at')->first(),
        ]);
    }

    /**
     * Live queue position and wait estimate for the patient's appointment today.
     *
     * @return array{appointment: Appointment, state: string, waitingAhead: int, estimatedMinutes: int, nowServing: int|null}|null
     */
    private function queueStatusFor($patient): ?array
    {
        $appointment = Appointment::query()
            ->forPatient($patient->id)
            ->forDate(now()->toDateString())
            ->with('physician.user')
            ->orderByDesc('queue_number')
            ->first();

        if ($appointment === null) {
            return null;
        }

        $isWaiting = in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED], true);

        $state = match (true) {
            $appointment->status === Appointment::STATUS_COMPLETED => 'completed',
            $appointment->called_at !== null => 'called',
            $isWaiting => 'waiting',
            default => 'closed',
        };

        if ($state === 'closed') {
            return null;
        }

        $slotMinutes = max((int) $appointment->physician?->appointment_slot_minutes, 1);

        $waitingAhead = $state === 'waiting'
            ? Appointment::query()
                ->forPhysician($appointment->physician_id)
                ->forDate($appointment->appointment_date->toDateString())
                ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                ->whereNull('called_at')
                ->where('queue_number', '<', $appointment->queue_number)
                ->count()
            : 0;

        $nowServing = Appointment::query()
            ->forPhysician($appointment->physician_id)
            ->forDate($appointment->appointment_date->toDateString())
            ->where(function ($query) {
                $query->whereNotNull('called_at')
                    ->orWhere('status', Appointment::STATUS_COMPLETED);
            })
            ->max('queue_number');

        return [
            'appointment' => $appointment,
            'state' => $state,
            'waitingAhead' => $waitingAhead,
            'estimatedMinutes' => $waitingAhead * $slotMinutes,
            'nowServing' => $nowServing !== null ? (int) $nowServing : null,
        ];
    }

    /**
     * The patient's read-only electronic medical record.
     */
    public function records(Request $request): View
    {
        $patient = $request->user()->patient;

        abort_if($patient === null, 404);

        return view('patient.records', [
            'patient' => $patient,
            'histories' => $patient->medicalHistories()->get(),
            'allergies' => $patient->allergies()->get(),
            'encounters' => $patient->encounters()
                ->with(['physician.user', 'vitals', 'prescriptions'])
                ->latest('encountered_at')
                ->get(),
            'prescriptions' => $patient->prescriptions()
                ->with('physician.user')
                ->latest('issued_at')
                ->get(),
        ]);
    }
}
