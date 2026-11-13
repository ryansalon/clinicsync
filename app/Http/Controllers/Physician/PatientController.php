<?php

namespace App\Http\Controllers\Physician;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
}
