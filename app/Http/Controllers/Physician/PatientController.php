<?php

namespace App\Http\Controllers\Physician;

use App\Http\Controllers\Controller;
use App\Models\Allergy;
use App\Models\MedicalHistory;
use App\Models\Patient;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * Retrieve a patient's historical record during a face-to-face consultation.
     */
    public function show(Request $request, Patient $patient): View
    {
        Gate::authorize('view', $patient);

        return view('physician.patients.show', [
            'patient' => $patient->loadMissing('user'),
            'histories' => $patient->medicalHistories()->get(),
            'allergies' => $patient->allergies()->get(),
            'encounters' => $patient->encounters()
                ->with(['physician.user', 'vitals', 'prescriptions'])
                ->latest('encountered_at')
                ->get(),
            'appointments' => $patient->appointments()
                ->with('physician.user')
                ->orderByDesc('appointment_date')
                ->limit(20)
                ->get(),
        ]);
    }

    /**
     * Document a new medication, food, or environmental allergy on the patient's chart.
     */
    public function storeAllergy(Request $request, Patient $patient): RedirectResponse
    {
        Gate::authorize('view', $patient);

        $data = $request->validate([
            'allergen' => ['required', 'string', 'max:255'],
            'severity' => ['required', 'string', Rule::in(Allergy::SEVERITIES)],
            'reaction' => ['nullable', 'string', 'max:500'],
        ]);

        $allergy = $patient->allergies()->create([
            'allergen' => trim($data['allergen']),
            'severity' => $data['severity'],
            'reaction' => isset($data['reaction']) && trim($data['reaction']) !== '' ? trim($data['reaction']) : null,
            'is_active' => true,
        ]);

        AuditLogger::log($request->user(), 'allergy.recorded', $allergy, null, $allergy->toArray());

        return redirect()
            ->route('physician.patients.show', $patient)
            ->with('status', 'Allergy recorded on patient chart.');
    }

    /**
     * Toggle an allergy between active and inactive status on the patient's chart.
     */
    public function toggleAllergy(Request $request, Patient $patient, Allergy $allergy): RedirectResponse
    {
        Gate::authorize('view', $patient);

        abort_unless($allergy->patient_id === $patient->id, 404);

        $before = ['is_active' => $allergy->is_active];
        $allergy->update(['is_active' => ! $allergy->is_active]);

        AuditLogger::log($request->user(), 'allergy.updated', $allergy, $before, ['is_active' => $allergy->is_active]);

        return redirect()
            ->route('physician.patients.show', $patient)
            ->with('status', $allergy->is_active ? 'Allergy marked active.' : 'Allergy marked inactive.');
    }

    /**
     * Record a medical, surgical, family, or social history item on the patient's chart.
     */
    public function storeHistory(Request $request, Patient $patient): RedirectResponse
    {
        Gate::authorize('view', $patient);

        $data = $request->validate([
            'condition_name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(MedicalHistory::TYPES)],
            'year_diagnosed' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'status' => ['required', 'string', Rule::in(['active', 'resolved', 'chronic'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $history = $patient->medicalHistories()->create([
            'condition_name' => trim($data['condition_name']),
            'type' => $data['type'],
            'year_diagnosed' => $data['year_diagnosed'] ?? null,
            'status' => $data['status'],
            'notes' => isset($data['notes']) && trim($data['notes']) !== '' ? trim($data['notes']) : null,
        ]);

        AuditLogger::log($request->user(), 'medical_history.recorded', $history, null, $history->toArray());

        return redirect()
            ->route('physician.patients.show', $patient)
            ->with('status', 'Medical history condition added to patient chart.');
    }
}
