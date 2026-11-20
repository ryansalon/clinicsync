<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWalkInRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Physician;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\AuditLogger;
use App\Services\SequenceGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    /**
     * Unified daily queue: online bookings and physical walk-ins together.
     */
    public function index(Request $request): View|JsonResponse
    {
        $rawDate = $request->query('date');
        $date = is_string($rawDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate) === 1
            ? rescue(fn () => Carbon::createFromFormat('Y-m-d', $rawDate)->toDateString(), now()->toDateString(), false)
            : now()->toDateString();

        $physicianId = $request->query('physician_id');

        $query = Appointment::query()
            ->forDate($date)
            ->with(['patient.user', 'physician.user', 'encounter'])
            ->orderBy('queue_number');

        if ($physicianId !== null && $physicianId !== '') {
            $query->forPhysician((int) $physicianId);
        }

        $queue = $query->get();
        $physicians = Physician::cachedRoster();

        $bookedCounts = Appointment::query()
            ->forDate($date)
            ->active()
            ->selectRaw('physician_id, count(*) as booked')
            ->groupBy('physician_id')
            ->pluck('booked', 'physician_id');

        $physicianCapacity = $physicians->map(function (Physician $physician) use ($bookedCounts) {
            $booked = (int) ($bookedCounts[$physician->id] ?? 0);
            $limit = (int) ($physician->daily_patient_limit ?? 0);

            return [
                'physician' => $physician,
                'booked' => $booked,
                'limit' => $limit,
                'remaining' => max(0, $limit - $booked),
            ];
        });

        $nextPatientNumber = SequenceGenerator::next(
            Patient::query()->orderByDesc('id')->value('patient_number'),
            'PT-',
        );

        $viewData = [
            'date' => $date,
            'physicianId' => $physicianId === '' ? null : $physicianId,
            'queue' => $queue,
            'physicians' => $physicians,
            'physicianCapacity' => $physicianCapacity,
            'nextPatientNumber' => $nextPatientNumber,
            'currentlyCalled' => $queue->where('status', Appointment::STATUS_CONFIRMED)
                ->filter(fn (Appointment $item) => $item->called_at !== null)
                ->values(),
            'pending' => $queue->where('status', Appointment::STATUS_PENDING)->count(),
            'confirmed' => $queue->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'completed' => $queue->where('status', Appointment::STATUS_COMPLETED)->count(),
            'noShow' => $queue->where('status', Appointment::STATUS_NO_SHOW)->count(),
            'waiting' => $queue->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED]),
            'walkIns' => $queue->where('source', 'walk_in')->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.queue.partials.board', $viewData)->render(),
            ]);
        }

        return view('admin.queue.index', $viewData);
    }

    /**
     * Register a physical walk-in against the same queue as online bookings.
     */
    public function store(StoreWalkInRequest $request): RedirectResponse
    {
        $user = $request->user();
        $patient = $this->createWalkInPatient($request);

        AuditLogger::log($user, 'user.created', $patient->user, null, [
            'role' => Role::Patient->value,
            'patient_number' => $patient->patient_number,
            'source' => 'walk_in',
        ]);

        $appointment = $this->appointments->book(
            $patient,
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
            ->with('status', 'Walk-in registered ('.$patient->patient_number.') and added to queue as #'.$appointment->queue_number.'.');
    }

    /**
     * Create a patient account and profile with an auto-generated PT-XXXXXX ID.
     */
    private function createWalkInPatient(StoreWalkInRequest $request): Patient
    {
        return SequenceGenerator::retryTransaction(function () use ($request): Patient {
            $latestNumber = Patient::query()
                ->orderByDesc('id')
                ->lockForUpdate()
                ->value('patient_number');

            $patientNumber = SequenceGenerator::next($latestNumber, 'PT-');
            $providedEmail = trim((string) $request->input('email', ''));
            $email = $providedEmail !== ''
                ? $providedEmail
                : strtolower(str_replace('-', '', $patientNumber)).'@walkin.clinicsync.local';

            $patientUser = User::create([
                'name' => trim((string) $request->input('patient_name')),
                'email' => $email,
                'phone' => $request->input('phone') ?: null,
                'password' => Str::password(32),
                'is_active' => true,
            ]);

            $patientUser->assignRole(Role::Patient->value);

            $patient = $patientUser->patient()->create([
                'patient_number' => $patientNumber,
                'date_of_birth' => (string) $request->input('date_of_birth'),
                'sex' => (string) $request->input('sex'),
            ]);

            return $patient->load('user');
        });
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
