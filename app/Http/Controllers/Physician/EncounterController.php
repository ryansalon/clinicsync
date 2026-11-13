<?php

namespace App\Http\Controllers\Physician;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEncounterRequest;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Services\AuditLogger;
use App\Services\SequenceGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EncounterController extends Controller
{
    /**
     * Consultation history with patient search.
     */
    public function index(Request $request): View
    {
        $physician = $request->user()->physician;

        abort_if($physician === null, 404);

        $search = trim((string) $request->query('q', ''));

        $encounters = Encounter::query()
            ->where('physician_id', $physician->id)
            ->with(['patient.user', 'vitals', 'prescriptions'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->whereHas('patient', fn ($q) => $q->where('patient_number', 'like', "%{$search}%"))
                        ->orWhereHas('patient.user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhere('diagnosis', 'like', "%{$search}%")
                        ->orWhere('encounter_number', 'like', "%{$search}%");
                });
            })
            ->latest('encountered_at')
            ->paginate(15)
            ->withQueryString();

        return view('physician.encounters.index', [
            'encounters' => $encounters,
            'search' => $search,
        ]);
    }

    /**
     * Open a consultation for a queued appointment, with the patient's EMR in view.
     */
    public function create(Request $request, Appointment $appointment): View
    {
        Gate::authorize('update', $appointment);

        $appointment->load(['patient.user', 'physician.user']);

        return view('physician.encounters.create', [
            'appointment' => $appointment,
            'patient' => $appointment->patient,
            'histories' => $appointment->patient->medicalHistories()->get(),
            'allergies' => $appointment->patient->activeAllergies()->get(),
            'previousEncounters' => $appointment->patient->encounters()
                ->with('physician.user')
                ->latest('encountered_at')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Record the encounter, its vitals and optionally close the appointment.
     */
    public function store(StoreEncounterRequest $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('update', $appointment);

        $user = $request->user();

        $encounter = DB::transaction(function () use ($request, $appointment, $user) {
            $completed = $request->boolean('mark_completed');

            $encounter = Encounter::create([
                'patient_id' => $appointment->patient_id,
                'physician_id' => $appointment->physician_id,
                'appointment_id' => $appointment->id,
                'encounter_number' => $this->nextEncounterNumber(),
                'encountered_at' => now(),
                'chief_complaint' => $request->input('chief_complaint') ?? $appointment->chief_complaint,
                'diagnosis' => $request->input('diagnosis'),
                'diagnosis_code' => $request->input('diagnosis_code'),
                'assessment' => $request->input('assessment'),
                'treatment_plan' => $request->input('treatment_plan'),
                'clinical_notes' => $request->input('clinical_notes'),
                'status' => $completed ? Encounter::STATUS_COMPLETED : Encounter::STATUS_IN_PROGRESS,
            ]);

            $this->syncVitals($encounter, $request);

            if ($completed) {
                $appointment->update([
                    'status' => Appointment::STATUS_COMPLETED,
                    'started_at' => $appointment->started_at ?? now(),
                    'finished_at' => now(),
                    'handled_by' => $user->id,
                ]);
            } elseif ($appointment->status === Appointment::STATUS_CONFIRMED) {
                $appointment->update([
                    'started_at' => now(),
                    'handled_by' => $user->id,
                ]);
            }

            return $encounter;
        });

        AuditLogger::log($user, 'encounter.created', $encounter, null, $encounter->toArray());

        return redirect()
            ->route('physician.encounters.show', $encounter)
            ->with('status', 'Encounter '.$encounter->encounter_number.' recorded.');
    }

    /**
     * Review a recorded encounter and its prescriptions.
     */
    public function show(Request $request, Encounter $encounter): View
    {
        Gate::authorize('view', $encounter);

        return view('physician.encounters.show', [
            'encounter' => $encounter->load([
                'patient.user',
                'physician.user',
                'vitals',
                'prescriptions',
                'appointment',
            ]),
        ]);
    }

    /**
     * Amend the diagnosis, assessment, treatment plan or clinical notes.
     */
    public function update(StoreEncounterRequest $request, Encounter $encounter): RedirectResponse
    {
        Gate::authorize('update', $encounter);

        $before = $encounter->only([
            'diagnosis', 'diagnosis_code', 'assessment', 'treatment_plan', 'clinical_notes', 'status',
        ]);

        DB::transaction(function () use ($request, $encounter) {
            $encounter->fill([
                'chief_complaint' => $request->input('chief_complaint'),
                'diagnosis' => $request->input('diagnosis'),
                'diagnosis_code' => $request->input('diagnosis_code'),
                'assessment' => $request->input('assessment'),
                'treatment_plan' => $request->input('treatment_plan'),
                'clinical_notes' => $request->input('clinical_notes'),
                'status' => $request->boolean('mark_completed')
                    ? Encounter::STATUS_COMPLETED
                    : $encounter->status,
            ])->save();

            if ($request->boolean('mark_completed') && $encounter->appointment !== null) {
                $encounter->appointment->update([
                    'status' => Appointment::STATUS_COMPLETED,
                    'finished_at' => now(),
                    'handled_by' => $request->user()->id,
                ]);
            }

            $this->syncVitals($encounter, $request);
        });

        AuditLogger::log(
            $request->user(),
            'encounter.updated',
            $encounter,
            $before,
            $encounter->fresh()->only(array_keys($before)),
        );

        return redirect()
            ->route('physician.encounters.show', $encounter)
            ->with('status', 'Encounter updated.');
    }

    /**
     * Persist vitals against the encounter, creating the row on first write.
     */
    private function syncVitals(Encounter $encounter, $request): void
    {
        $attributes = array_filter([
            'temperature_c' => $request->input('temperature_c'),
            'systolic_bp' => $request->input('systolic_bp'),
            'diastolic_bp' => $request->input('diastolic_bp'),
            'heart_rate' => $request->input('heart_rate'),
            'respiratory_rate' => $request->input('respiratory_rate'),
            'weight_kg' => $request->input('weight_kg'),
            'height_cm' => $request->input('height_cm'),
            'spo2' => $request->input('spo2'),
        ], fn ($value) => $value !== null && $value !== '');

        if ($attributes === []) {
            return;
        }

        // updateOrCreate is race-safe: on a concurrent first-write it catches the
        // unique constraint violation on encounter_id and re-reads the winner.
        $encounter->vitals()->updateOrCreate([], $attributes);
    }

    private function nextEncounterNumber(): string
    {
        $latest = Encounter::query()->orderByDesc('id')->lockForUpdate()->value('encounter_number');

        return SequenceGenerator::next($latest, 'ENC-');
    }
}
