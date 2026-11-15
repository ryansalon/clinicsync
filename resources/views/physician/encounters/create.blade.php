@extends('layouts.app')

@section('title', 'New consultation')

@section('content')
    <x-page-header title="New consultation"
                   description="{{ $appointment->appointment_date->isoFormat('dddd, MMM D, YYYY') }} · {{ date('g:i A', strtotime($appointment->start_time)) }} – {{ date('g:i A', strtotime($appointment->end_time)) }} · Queue #{{ $appointment->queue_number }}">
        <x-slot:actions>
            <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
        </x-slot:actions>
    </x-page-header>

    @if ($allergies->isNotEmpty())
        <x-alert type="error" class="mb-6" title="Active allergies on file ({{ $allergies->count() }})">
            <ul class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                @foreach ($allergies as $allergy)
                    <li>
                        <span class="font-semibold">{{ $allergy->allergen }}</span>
                        ({{ \Illuminate\Support\Str::headline($allergy->severity) }}@if ($allergy->reaction) — {{ $allergy->reaction }}@endif)
                    </li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Left column: patient record at a glance --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Patient summary --}}
            <div class="card">
                <div class="card-header">
                    <x-card-title>Patient</x-card-title>
                    <div class="mt-2 flex items-center gap-3">
                        <x-avatar :name="$patient->user->name" size="lg" />
                        <div class="min-w-0">
                            <p class="text-base font-semibold tracking-tight text-slate-900">{{ $patient->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $patient->patient_number }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="section-title">Age</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $patient->age ?? '—' }} years old</dd>
                        </div>
                        <div>
                            <dt class="section-title">Sex</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $patient->sex ? \Illuminate\Support\Str::headline($patient->sex) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="section-title">Blood type</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $patient->blood_type ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="section-title">Civil status</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $patient->civil_status ? \Illuminate\Support\Str::headline($patient->civil_status) : '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="section-title">Address</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $patient->address ?: '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <dt class="section-title">Emergency contact</dt>
                            <dd class="mt-1 text-sm text-slate-700">
                                {{ $patient->emergency_contact_name ?: '—' }}
                                @if ($patient->emergency_contact_phone)
                                    <span class="text-slate-500">· {{ $patient->emergency_contact_phone }}</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Allergies --}}
            <div class="card">
                <div class="card-header flex flex-wrap items-center justify-between gap-2">
                    <x-card-title description="Active allergies only.">Allergies</x-card-title>
                    <span class="chip">
                        <x-icon name="exclamation-triangle" class="h-4 w-4 text-slate-400" />
                        {{ $allergies->count() }} on file
                    </span>
                </div>
                <div class="card-body">
                    @forelse ($allergies as $allergy)
                        <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 {{ $loop->last ? '' : 'mb-3' }}">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-rose-800">{{ $allergy->allergen }}</p>
                                <span class="badge badge-{{ $allergy->severity }}">
                                    {{ \Illuminate\Support\Str::headline($allergy->severity) }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-rose-700">{{ $allergy->reaction }}</p>
                        </div>
                    @empty
                        <x-empty icon="shield-check" title="No known active allergies"
                                 description="No active allergies are recorded for this patient." />
                    @endforelse
                </div>
            </div>

            {{-- Medical history --}}
            <div class="card">
                <div class="card-header">
                    <x-card-title description="Past conditions recorded by the clinic.">Medical history</x-card-title>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr>
                                <th class="table-head">Condition</th>
                                <th class="table-head">Type</th>
                                <th class="table-head">Year</th>
                                <th class="table-head">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($histories as $history)
                                <tr class="table-row">
                                    <td class="table-cell font-medium text-slate-900">{{ $history->condition_name }}</td>
                                    <td class="table-cell">{{ \Illuminate\Support\Str::headline($history->type) }}</td>
                                    <td class="table-cell">{{ $history->year_diagnosed ?? '—' }}</td>
                                    <td class="table-cell">{{ $history->status ? \Illuminate\Support\Str::headline($history->status) : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-12">
                                        <x-empty icon="document" title="No medical history recorded"
                                                 description="Past conditions recorded by the clinic will appear here." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Previous consultations --}}
            <div class="card">
                <div class="card-header flex flex-wrap items-center justify-between gap-3">
                    <x-card-title description="Most recent 5 encounters.">Previous consultations</x-card-title>
                    <a href="{{ route('physician.patients.show', $patient) }}"
                       class="inline-flex items-center gap-1 text-sm font-medium text-teal-600 transition hover:text-teal-700">
                        Full record
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($previousEncounters as $previous)
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 transition-colors hover:bg-slate-50/70">
                            <div class="min-w-0">
                                <p class="text-sm">
                                    <a href="{{ route('physician.encounters.show', $previous) }}" class="font-medium text-slate-900 transition hover:text-teal-700">{{ $previous->encounter_number }}</a>
                                    <span class="ml-2 text-xs text-slate-500">{{ $previous->encountered_at->isoFormat('MMM D, YYYY') }}</span>
                                </p>
                                <p class="mt-0.5 truncate text-sm text-slate-600">
                                    {{ $previous->diagnosis }}
                                    <span class="text-xs text-slate-400">· {{ $previous->physician->user->name }}</span>
                                </p>
                            </div>
                            <span class="badge badge-{{ $previous->status }}">{{ \Illuminate\Support\Str::headline($previous->status) }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-6">
                            <x-empty icon="clipboard" title="No previous consultations"
                                     description="This patient's consultation history will appear here." />
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right column: consultation form --}}
        <div>
            <div class="card">
                <div class="card-header">
                    <x-card-title description="Record the diagnosis, plan and vitals for this visit.">Consultation form</x-card-title>
                </div>

                <form method="POST" action="{{ route('physician.encounters.store', $appointment) }}" data-dirty-guard>
                    @csrf

                    <div class="card-body space-y-5">
                        <p class="section-title">Assessment</p>

                        <div>
                            <label for="chief_complaint" class="label">Chief complaint</label>
                            <input id="chief_complaint" name="chief_complaint" type="text"
                                   class="input @error('chief_complaint') input-invalid @enderror"
                                   value="{{ old('chief_complaint', $appointment->chief_complaint) }}"
                                   placeholder="Reason for consultation">
                            @error('chief_complaint')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="diagnosis" class="label">Diagnosis <span class="text-rose-500">*</span></label>
                            <input id="diagnosis" name="diagnosis" type="text" required
                                   class="input @error('diagnosis') input-invalid @enderror"
                                   value="{{ old('diagnosis') }}" placeholder="Primary diagnosis">
                            @error('diagnosis')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="diagnosis_code" class="label">Diagnosis code</label>
                            <input id="diagnosis_code" name="diagnosis_code" type="text"
                                   class="input @error('diagnosis_code') input-invalid @enderror"
                                   value="{{ old('diagnosis_code') }}" placeholder="ICD-10, optional">
                            @error('diagnosis_code')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="assessment" class="label">Assessment</label>
                            <textarea id="assessment" name="assessment" rows="3"
                                      class="input @error('assessment') input-invalid @enderror"
                                      placeholder="Findings and clinical reasoning">{{ old('assessment') }}</textarea>
                            @error('assessment')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="divider"></div>
                        <p class="section-title">Plan &amp; notes</p>

                        <div>
                            <label for="treatment_plan" class="label">Treatment plan</label>
                            <textarea id="treatment_plan" name="treatment_plan" rows="3"
                                      class="input @error('treatment_plan') input-invalid @enderror"
                                      placeholder="Plan of care for this patient">{{ old('treatment_plan') }}</textarea>
                            @error('treatment_plan')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="clinical_notes" class="label">Clinical notes</label>
                            <textarea id="clinical_notes" name="clinical_notes" rows="3"
                                      class="input @error('clinical_notes') input-invalid @enderror"
                                      placeholder="Additional notes">{{ old('clinical_notes') }}</textarea>
                            @error('clinical_notes')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <fieldset class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                            <legend class="section-title px-1">Vitals</legend>

                            @php
                                // Rebuild the ft/in fields from a submitted centimeter value
                                // when the form is re-rendered after a validation error.
                                $legacyCm = old('height_cm');
                                $presetFeet = old('height_feet', is_numeric($legacyCm) ? (int) floor($legacyCm / 30.48) : '');
                                $presetInches = old('height_inches', is_numeric($legacyCm) ? round((($legacyCm / 2.54) - ((int) floor($legacyCm / 30.48)) * 12), 1) : '');
                            @endphp

                            <div class="mt-3 grid grid-cols-2 gap-4">
                                <div>
                                    <label for="temperature_c" class="label">Temperature</label>
                                    <div class="relative">
                                        <input id="temperature_c" name="temperature_c" type="number" step="0.1"
                                               class="input pr-14 @error('temperature_c') input-invalid @enderror"
                                               value="{{ old('temperature_c') }}" placeholder="e.g. 37.2">
                                        <span class="input-unit">°C</span>
                                    </div>
                                    @error('temperature_c')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="systolic_bp" class="label">Systolic BP</label>
                                    <div class="relative">
                                        <input id="systolic_bp" name="systolic_bp" type="number"
                                               class="input pr-16 @error('systolic_bp') input-invalid @enderror"
                                               value="{{ old('systolic_bp') }}" placeholder="e.g. 120">
                                        <span class="input-unit">mmHg</span>
                                    </div>
                                    @error('systolic_bp')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="diastolic_bp" class="label">Diastolic BP</label>
                                    <div class="relative">
                                        <input id="diastolic_bp" name="diastolic_bp" type="number"
                                               class="input pr-16 @error('diastolic_bp') input-invalid @enderror"
                                               value="{{ old('diastolic_bp') }}" placeholder="e.g. 80">
                                        <span class="input-unit">mmHg</span>
                                    </div>
                                    @error('diastolic_bp')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="heart_rate" class="label">Heart rate</label>
                                    <div class="relative">
                                        <input id="heart_rate" name="heart_rate" type="number"
                                               class="input pr-14 @error('heart_rate') input-invalid @enderror"
                                               value="{{ old('heart_rate') }}" placeholder="e.g. 84">
                                        <span class="input-unit">bpm</span>
                                    </div>
                                    @error('heart_rate')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="respiratory_rate" class="label">Respiratory rate</label>
                                    <div class="relative">
                                        <input id="respiratory_rate" name="respiratory_rate" type="number"
                                               class="input pr-14 @error('respiratory_rate') input-invalid @enderror"
                                               value="{{ old('respiratory_rate') }}" placeholder="e.g. 18">
                                        <span class="input-unit">/min</span>
                                    </div>
                                    @error('respiratory_rate')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="weight_kg" class="label">Weight</label>
                                    <div class="relative">
                                        <input id="weight_kg" name="weight_kg" type="number" step="0.1" min="1" max="500"
                                               class="input pr-12 @error('weight_kg') input-invalid @enderror"
                                               value="{{ old('weight_kg') }}" placeholder="e.g. 62.5">
                                        <span class="input-unit">kg</span>
                                    </div>
                                    @error('weight_kg')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Height is entered in feet & inches; the record stores cm --}}
                                <div class="col-span-2">
                                    <label for="height_feet" class="label">Height</label>
                                    <div class="flex items-center gap-2">
                                        <div class="relative flex-1">
                                            <input id="height_feet" name="height_feet" type="number" min="0" max="8" step="1"
                                                   class="input pr-12 @error('height_feet') input-invalid @enderror @error('height_cm') input-invalid @enderror"
                                                   value="{{ $presetFeet }}" placeholder="5">
                                            <span class="input-unit">ft</span>
                                        </div>
                                        <div class="relative flex-1">
                                            <input id="height_inches" name="height_inches" type="number" min="0" max="11.9" step="0.5"
                                                   class="input pr-12 @error('height_inches') input-invalid @enderror @error('height_cm') input-invalid @enderror"
                                                   value="{{ $presetInches }}" placeholder="10">
                                            <span class="input-unit">in</span>
                                        </div>
                                        <input type="hidden" name="height_cm" id="height_cm" value="{{ old('height_cm', '') }}">
                                    </div>
                                    <p class="helper" id="height-preview">Enter feet and inches — the record stores centimeters.</p>
                                    @error('height_feet')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                    @error('height_inches')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                    @error('height_cm')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="spo2" class="label">SpO₂</label>
                                    <div class="relative">
                                        <input id="spo2" name="spo2" type="number" min="40" max="100"
                                               class="input pr-12 @error('spo2') input-invalid @enderror"
                                               value="{{ old('spo2') }}" placeholder="e.g. 98">
                                        <span class="input-unit">%</span>
                                    </div>
                                    @error('spo2')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        <label for="mark_completed" class="label flex items-center gap-2">
                            <input id="mark_completed" name="mark_completed" type="checkbox" value="1"
                                   class="h-4 w-4 shrink-0 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                   @checked(old('mark_completed'))>
                            Mark this consultation as completed and close the appointment
                        </label>
                    </div>

                    <div class="sticky bottom-0 z-10 flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                        <a href="{{ route('physician.dashboard') }}" class="btn btn-secondary">
                            <x-icon name="x" class="h-4 w-4" />
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            Save encounter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            var feetInput = document.getElementById('height_feet');
            var inchesInput = document.getElementById('height_inches');
            var hiddenCm = document.getElementById('height_cm');
            var preview = document.getElementById('height-preview');

            if (!feetInput || !inchesInput || !hiddenCm) {
                return;
            }

            var DEFAULT_HINT = 'Enter feet and inches — the record stores centimeters.';

            function normalizeHeight() {
                var feet = parseFloat(feetInput.value) || 0;
                var inches = parseFloat(inchesInput.value) || 0;

                // Carry over whole feet when inches reach12.
                if (inches >= 12) {
                    feet += Math.floor(inches / 12);
                    inches = inches % 12;
                    feetInput.value = feet || '';
                    inchesInput.value = inches || '';
                }

                if (feet === 0 && inches === 0) {
                    hiddenCm.value = '';

                    if (preview) {
                        preview.textContent = DEFAULT_HINT;
                    }

                    return;
                }

                var cm = Math.round((feet * 30.48 + inches * 2.54) * 10) / 10;

                hiddenCm.value = String(cm);

                if (preview) {
                    preview.textContent = 'Recorded as ≈ ' + cm + ' cm';
                }
            }

            feetInput.addEventListener('input', normalizeHeight);
            inchesInput.addEventListener('input', normalizeHeight);

            // Reflect any prefilled values (e.g. after a validation error).
            normalizeHeight();
        })();
    </script>
@endpush
