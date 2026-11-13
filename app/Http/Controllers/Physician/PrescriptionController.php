<?php

namespace App\Http\Controllers\Physician;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrescriptionRequest;
use App\Models\Encounter;
use App\Models\Prescription;
use App\Services\AuditLogger;
use App\Services\SequenceGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PrescriptionController extends Controller
{
    /**
     * Issue a digital prescription attached to the encounter log.
     */
    public function store(StorePrescriptionRequest $request, Encounter $encounter): RedirectResponse
    {
        Gate::authorize('update', $encounter);

        $prescription = DB::transaction(function () use ($request, $encounter) {
            return $encounter->prescriptions()->create([
                'patient_id' => $encounter->patient_id,
                'physician_id' => $encounter->physician_id,
                'prescription_number' => $this->nextPrescriptionNumber(),
                'medication_name' => $request->input('medication_name'),
                'strength' => $request->input('strength'),
                'dosage_form' => $request->input('dosage_form'),
                'dosage' => $request->input('dosage'),
                'frequency' => $request->input('frequency'),
                'route' => $request->input('route') ?? 'oral',
                'duration' => $request->input('duration'),
                'quantity' => $request->input('quantity'),
                'instructions' => $request->input('instructions'),
                'issued_at' => now(),
            ]);
        });

        AuditLogger::log($request->user(), 'prescription.issued', $prescription, null, $prescription->toArray());

        return redirect()
            ->route('physician.encounters.show', $encounter)
            ->with('status', 'Prescription '.$prescription->prescription_number.' issued.');
    }

    private function nextPrescriptionNumber(): string
    {
        $latest = Prescription::query()->orderByDesc('id')->lockForUpdate()->value('prescription_number');

        return SequenceGenerator::next($latest, 'RX-');
    }
}
