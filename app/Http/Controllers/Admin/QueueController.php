<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWalkInRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Physician;
use App\Services\AppointmentService;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    /**
     * Autocomplete endpoint for walk-in registration.
     */
    public function searchPatients(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $patients = Patient::query()
            ->with('user')
            ->where(function ($builder) use ($query) {
                $builder->where('patient_number', 'like', "%{$query}%")
                    ->orWhereHas('user', function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('email', 'like', "%{$query}%");
                    });
            })
            ->limit(10)
            ->get()
            ->map(fn (Patient $patient) => [
                'id' => $patient->id,
                'name' => $patient->user?->name ?? 'Unknown',
                'patient_number' => $patient->patient_number,
                'label' => ($patient->user?->name ?? 'Unknown').' ('.$patient->patient_number.')',
            ]);

        return response()->json($patients);
    }

    /**
     * Unified daily queue: online bookings and physical walk-ins together.
     */
    public function index(Request $request): View
    {
        $date = (string) $request->query('date', now()->toDateString());
        $physicianId = $request->query('physician_id');

        $query = Appointment::query()
            ->forDate($date)
            ->with(['patient.user', 'physician.user', 'encounter'])
            ->orderBy('queue_number');

        if ($physicianId !== null && $physicianId !== '') {
            $query->forPhysician((int) $physicianId);
        }

        $queue = $query->get();

        return view('admin.queue.index', [
            'date' => $date,
            'physicianId' => $physicianId === '' ? null : $physicianId,
            'queue' => $queue,
            'physicians' => Physician::cachedRoster(),
            'pending' => $queue->where('status', Appointment::STATUS_PENDING)->count(),
            'confirmed' => $queue->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'completed' => $queue->where('status', Appointment::STATUS_COMPLETED)->count(),
            'noShow' => $queue->where('status', Appointment::STATUS_NO_SHOW)->count(),
            'waiting' => $queue->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED]),
            'walkIns' => $queue->where('source', 'walk_in')->count(),
        ]);
    }

    /**
     * Register a physical walk-in against the same queue as online bookings.
     */
    public function store(StoreWalkInRequest $request): RedirectResponse
    {
        $user = $request->user();

        $appointment = $this->appointments->book(
            Patient::findOrFail((int) $request->input('patient_id')),
            Physician::findOrFail((int) $request->input('physician_id')),
            [
                'appointment_date' => (string) $request->input('appointment_date'),
                'start_time' => (string) $request->input('start_time'),
                'source' => 'walk_in',
                'status' => Appointment::STATUS_CONFIRMED,
                'chief_complaint' => $request->input('chief_complaint'),
                'reason' => $request->input('reason'),
                'booked_by' => $user->id,
            ],
        );

        AuditLogger::log($user, 'appointment.walk_in_registered', $appointment, null, $appointment->toArray());

        return redirect()
            ->route('admin.queue.index', ['date' => $appointment->appointment_date->toDateString()])
            ->with('status', 'Walk-in added to queue as #'.$appointment->queue_number.'.');
    }

    /**
     * Confirm, reschedule or close out a queued appointment.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Rescheduling and status changes commit together so a partial failure
        // can never leave an appointment half-moved.
        $appointment = DB::transaction(function () use ($request, $user, $data, $appointment) {
            if ($request->anyFilled(['appointment_date', 'start_time', 'physician_id'])) {
                $appointment = $this->appointments->reschedule($appointment, [
                    'appointment_date' => (string) ($data['appointment_date'] ?? $appointment->appointment_date->toDateString()),
                    'start_time' => (string) ($data['start_time'] ?? substr($appointment->start_time, 0, 5)),
                    'physician_id' => (int) ($data['physician_id'] ?? $appointment->physician_id),
                ], $user->id);
            }

            if (isset($data['status'])) {
                $completed = $data['status'] === Appointment::STATUS_COMPLETED;

                $appointment->update([
                    'status' => $data['status'],
                    'handled_by' => $user->id,
                    'finished_at' => $completed ? ($appointment->finished_at ?? now()) : null,
                ]);
            }

            return $appointment;
        });

        $returnDate = $appointment->appointment_date->toDateString();

        AuditLogger::log($user, 'appointment.updated', $appointment, null, $appointment->fresh()->toArray());

        return redirect()
            ->route('admin.queue.index', ['date' => $request->input('date', $request->query('date', $returnDate))])
            ->with('status', 'Queue updated.');
    }

    /**
     * Stamp the moment a patient is called forward.
     */
    public function call(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        $appointment->update([
            'called_at' => now(),
            'handled_by' => $request->user()->id,
            'status' => $appointment->status === Appointment::STATUS_PENDING
                ? Appointment::STATUS_CONFIRMED
                : $appointment->status,
        ]);

        AuditLogger::log($request->user(), 'appointment.called', $appointment);

        return redirect()
            ->route('admin.queue.index', ['date' => $request->input('date', $request->query('date', $appointment->appointment_date->toDateString()))])
            ->with('status', 'Patient called forward.');
    }

    /**
     * Remove a queued appointment entirely (administrators only).
     */
    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('delete', $appointment);

        $appointment->delete();

        AuditLogger::log($request->user(), 'appointment.deleted', $appointment);

        return redirect()
            ->route('admin.queue.index')
            ->with('status', 'Appointment removed from the queue.');
    }
}
