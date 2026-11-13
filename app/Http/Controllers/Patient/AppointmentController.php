<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\RescheduleAppointmentRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Physician;
use App\Services\AppointmentService;
use App\Services\AuditLogger;
use App\Services\PhysicianAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    /**
     * List the authenticated patient's own appointments.
     */
    public function index(Request $request): View
    {
        $patient = $request->user()->patient;

        abort_if($patient === null, 404);

        $status = $request->query('status');

        $appointments = Appointment::query()
            ->forPatient($patient->id)
            ->with('physician.user')
            ->when($status !== null && in_array($status, Appointment::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->paginate(10)
            ->withQueryString();

        return view('patient.appointments.index', [
            'appointments' => $appointments,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Show the booking form with live physician availability.
     */
    public function create(Request $request): View
    {
        $physicians = Physician::query()->with('user')->orderBy('id')->get();

        $requestedDate = $request->query('date');
        $selectedDate = is_string($requestedDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate) === 1
            ? $requestedDate
            : null;

        return view('patient.appointments.create', [
            'physicians' => $physicians,
            'selectedPhysician' => Physician::find($request->query('physician_id')),
            'selectedDate' => $selectedDate,
            'formVisible' => ! empty($request->old()) || $selectedDate !== null,
        ]);
    }

    /**
     * Reserve a slot after server-side availability validation.
     */
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $user = $request->user();

        $appointment = $this->appointments->book(
            $user->patient,
            Physician::findOrFail((int) $request->input('physician_id')),
            [
                'appointment_date' => (string) $request->input('appointment_date'),
                'start_time' => (string) $request->input('start_time'),
                'chief_complaint' => $request->input('chief_complaint'),
                'reason' => $request->input('reason'),
                'booked_by' => $user->id,
            ],
        );

        AuditLogger::log($user, 'appointment.booked', $appointment, null, $appointment->toArray());

        return redirect()
            ->route('patient.appointments.show', $appointment)
            ->with('status', 'Appointment booked. Queue number #'.$appointment->queue_number.'.');
    }

    /**
     * JSON slot availability used by the booking form.
     */
    public function availability(Request $request, PhysicianAvailability $availability): JsonResponse
    {
        $request->validate([
            'physician_id' => ['required', 'integer', 'exists:physicians,id'],
            'date' => ['required', 'date'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ]);

        $physician = Physician::findOrFail((int) $request->query('physician_id'));

        $exceptAppointmentId = null;

        if ($request->filled('appointment_id')) {
            $appointment = Appointment::findOrFail((int) $request->query('appointment_id'));

            abort_unless($appointment->patient_id === $request->user()->patient?->id, 403);

            $exceptAppointmentId = $appointment->id;
        }

        return response()->json($availability->slots(
            $physician,
            (string) $request->query('date'),
            $exceptAppointmentId,
        ));
    }

    /**
     * Show a single appointment belonging to the authenticated patient.
     */
    public function show(Request $request, Appointment $appointment): View
    {
        Gate::authorize('view', $appointment);

        return view('patient.appointments.show', [
            'appointment' => $appointment->load(['physician.user', 'encounter.prescriptions']),
        ]);
    }

    /**
     * Show the reschedule form with live slot availability.
     */
    public function reschedule(Appointment $appointment): View
    {
        Gate::authorize('update', $appointment);

        return view('patient.appointments.reschedule', [
            'appointment' => $appointment->load(['physician.user']),
        ]);
    }

    /**
     * Move a future appointment to a new date/time slot.
     */
    public function rescheduleUpdate(RescheduleAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        $user = $request->user();
        $before = $appointment->only(['appointment_date', 'start_time', 'queue_number', 'status']);

        $updated = $this->appointments->reschedule($appointment, [
            'appointment_date' => (string) $request->input('appointment_date'),
            'start_time' => (string) $request->input('start_time'),
        ], $user->id);

        AuditLogger::log($user, 'appointment.rescheduled', $updated, $before, $updated->fresh()->toArray());

        return redirect()
            ->route('patient.appointments.show', $updated)
            ->with(
                'status',
                'Appointment moved to '.$updated->appointment_date->isoFormat('MMM D, YYYY')
                    .' at '.date('g:i A', strtotime($updated->start_time))
                    .'. New queue number #'.$updated->queue_number.'.'
            );
    }

    /**
     * Cancel a future appointment that is still pending or confirmed.
     */
    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        $this->appointments->cancel($appointment, $request->user()->id);

        AuditLogger::log($request->user(), 'appointment.cancelled', $appointment);

        return redirect()
            ->route('patient.appointments.index')
            ->with('status', 'Appointment cancelled.');
    }
}
