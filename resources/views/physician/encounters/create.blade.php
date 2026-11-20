@extends('layouts.app')

@section('title', 'New consultation')

@section('content')
    <x-page-header title="New consultation"
                   description="{{ $appointment->appointment_date->isoFormat('dddd, MMM D, YYYY') }} · {{ date('g:i A', strtotime($appointment->start_time)) }} – {{ date('g:i A', strtotime($appointment->end_time)) }} · Queue #{{ $appointment->queue_number }}">
        <x-slot:actions>
            <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
        </x-slot:actions>
    </x-page-header>

    {{-- Unified active allergy alert at top of clinical workspace --}}
    @if ($allergies->isNotEmpty())
        <x-alert type="error" class="mb-5" title="Active allergies on file ({{ $allergies->count() }})">
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

    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Primary workspace (7/12): Consultation SOAP & Vitals --}}
        <div class="lg:col-span-7">
            <div class="card">
                <div class="card-header">
                    <x-card-title description="Record the diagnosis, clinical plan, and vitals. Save first to issue prescriptions on the encounter chart.">Consultation form</x-card-title>
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

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label for="diagnosis" class="label">Diagnosis <span class="text-danger">*</span></label>
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
                                       class="input font-mono @error('diagnosis_code') input-invalid @enderror"
                                       value="{{ old('diagnosis_code') }}" placeholder="ICD-10">
                                @error('diagnosis_code')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
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
                            <textarea id="clinical_notes" name="clinical_notes" rows="2"
                                      class="input @error('clinical_notes') input-invalid @enderror"
                                      placeholder="Additional observations">{{ old('clinical_notes') }}</textarea>
                            @error('clinical_notes')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Hairline-divided Vitals section (no nested bordered card box) --}}
                        <fieldset class="border-t border-hairline pt-5">
                            <legend class="section-title">Vitals</legend>

                            @php
                                $legacyCm = old('height_cm');
                                $presetFeet = old('height_feet', is_numeric($legacyCm) ? (int) floor($legacyCm / 30.48) : '');
                                $presetInches = old('height_inches', is_numeric($legacyCm) ? round((($legacyCm / 2.54) - ((int) floor($legacyCm / 30.48)) * 12), 1) : '');
                            @endphp

                            <div class="mt-3.5 grid grid-cols-2 gap-4 sm:grid-cols-3">
                                <div>
                                    <label for="temperature_c" class="label">Temperature</label>
                                    <div class="relative">
                                        <input id="temperature_c" name="temperature_c" type="number" step="0.1"
                                               class="input cs-num pr-12 @error('temperature_c') input-invalid @enderror"
                                               value="{{ old('temperature_c') }}" placeholder="37.2">
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
                                               class="input cs-num pr-16 @error('systolic_bp') input-invalid @enderror"
                                               value="{{ old('systolic_bp') }}" placeholder="120">
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
                                               class="input cs-num pr-16 @error('diastolic_bp') input-invalid @enderror"
                                               value="{{ old('diastolic_bp') }}" placeholder="80">
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
                                               class="input cs-num pr-14 @error('heart_rate') input-invalid @enderror"
                                               value="{{ old('heart_rate') }}" placeholder="84">
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
                                               class="input cs-num pr-14 @error('respiratory_rate') input-invalid @enderror"
                                               value="{{ old('respiratory_rate') }}" placeholder="18">
                                        <span class="input-unit">/min</span>
                                    </div>
                                    @error('respiratory_rate')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="spo2" class="label">SpO₂</label>
                                    <div class="relative">
                                        <input id="spo2" name="spo2" type="number" min="40" max="100"
                                               class="input cs-num pr-10 @error('spo2') input-invalid @enderror"
                                               value="{{ old('spo2') }}" placeholder="98">
                                        <span class="input-unit">%</span>
                                    </div>
                                    @error('spo2')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="weight_kg" class="label">Weight</label>
                                    <div class="relative">
                                        <input id="weight_kg" name="weight_kg" type="number" step="0.1" min="1" max="500"
                                               class="input cs-num pr-12 @error('weight_kg') input-invalid @enderror"
                                               value="{{ old('weight_kg') }}" placeholder="62.5">
                                        <span class="input-unit">kg</span>
                                    </div>
                                    @error('weight_kg')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="col-span-2">
                                    <p class="label">Height</p>
                                    <div class="flex items-center gap-2">
                                        <div class="relative flex-1">
                                            <label for="height_feet" class="sr-only">Height in feet</label>
                                            <input id="height_feet" name="height_feet" type="number" min="0" max="8" step="1"
                                                   class="input cs-num pr-10 @error('height_feet') input-invalid @enderror @error('height_cm') input-invalid @enderror"
                                                   value="{{ $presetFeet }}" placeholder="5">
                                            <span class="input-unit">ft</span>
                                        </div>
                                        <div class="relative flex-1">
                                            <label for="height_inches" class="sr-only">Height in inches</label>
                                            <input id="height_inches" name="height_inches" type="number" min="0" max="11.9" step="0.5"
                                                   class="input cs-num pr-10 @error('height_inches') input-invalid @enderror @error('height_cm') input-invalid @enderror"
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
                            </div>
                        </fieldset>

                        <div class="border-t border-hairline pt-4">
                            <label for="mark_completed" class="label mb-0 flex cursor-pointer items-center gap-2">
                                <input id="mark_completed" name="mark_completed" type="checkbox" value="1"
                                       class="h-4 w-4 shrink-0 rounded border-hairline-2 accent-brand"
                                       @checked(old('mark_completed'))>
                                <span>Mark this consultation as completed and close the appointment (you can still add prescriptions right after saving)</span>
                            </label>
                        </div>
                    </div>

                    <div class="sticky bottom-0 z-10 flex items-center justify-end gap-3 rounded-b-lg border-t border-hairline bg-surface/95 px-5 py-4 backdrop-blur-xs">
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

        {{-- Sticky tabbed EMR reference rail (5/12): cross-check patient chart without losing scroll position --}}
        <aside class="lg:col-span-5 lg:sticky lg:top-0 lg:self-start">
            <div class="card overflow-hidden" data-tabs>
                <div class="card-header flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <x-avatar :name="$patient->user->name" size="md" />
                        <div class="min-w-0">
                            <h2 class="truncate text-sm font-semibold tracking-tight text-bright">{{ $patient->user->name }}</h2>
                            <p class="font-mono text-xs text-meta">{{ $patient->patient_number }}</p>
                        </div>
                    </div>
                    <a href="{{ route('physician.patients.show', $patient) }}"
                       class="shrink-0 text-xs font-semibold text-brand hover:underline">
                        Full chart &rarr;
                    </a>
                </div>

                {{-- Tab navigation --}}
                <div class="border-b border-hairline bg-canvas px-4 py-2.5">
                    <div class="tab-strip w-full" role="tablist" aria-label="Patient reference chart sections">
                        <button type="button" role="tab" id="emr-tab-profile" aria-controls="emr-panel-profile" aria-selected="true" tabindex="0"
                                data-tab-trigger="profile" class="tab-btn tab-btn-active flex-1">
                            Profile
                        </button>
                        <button type="button" role="tab" id="emr-tab-history" aria-controls="emr-panel-history" aria-selected="false" tabindex="-1"
                                data-tab-trigger="history" class="tab-btn flex-1">
                            History ({{ $histories->count() }})
                        </button>
                        <button type="button" role="tab" id="emr-tab-visits" aria-controls="emr-panel-visits" aria-selected="false" tabindex="-1"
                                data-tab-trigger="visits" class="tab-btn flex-1">
                            Visits ({{ $previousEncounters->count() }})
                        </button>
                    </div>
                </div>

                {{-- Tab 1: Demographics & Contact --}}
                <div id="emr-panel-profile" role="tabpanel" aria-labelledby="emr-tab-profile" data-tab-panel="profile" class="card-body">
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3.5 text-sm">
                        <div>
                            <dt class="section-title">Age / Sex</dt>
                            <dd class="mt-0.5 text-bright cs-num">
                                {{ $patient->age ?? '—' }} yrs · {{ $patient->sex ? \Illuminate\Support\Str::headline($patient->sex) : '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="section-title">Blood type</dt>
                            <dd class="mt-0.5 font-mono font-semibold text-bright">{{ $patient->blood_type ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="section-title">Civil status</dt>
                            <dd class="mt-0.5 text-read">{{ $patient->civil_status ? \Illuminate\Support\Str::headline($patient->civil_status) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="section-title">Emergency contact</dt>
                            <dd class="mt-0.5 text-read">
                                {{ $patient->emergency_contact_name ?: '—' }}
                                @if ($patient->emergency_contact_phone)
                                    <span class="block font-mono text-xs text-meta">{{ $patient->emergency_contact_phone }}</span>
                                @endif
                            </dd>
                        </div>
                        <div class="col-span-2 border-t border-hairline pt-3">
                            <dt class="section-title">Address</dt>
                            <dd class="mt-0.5 text-read">{{ $patient->address ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Tab 2: Medical History --}}
                <div id="emr-panel-history" role="tabpanel" aria-labelledby="emr-tab-history" data-tab-panel="history" hidden>
                    @if ($histories->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <caption class="sr-only">Patient medical history</caption>
                                <thead>
                                    <tr>
                                        <th scope="col" class="table-head">Condition</th>
                                        <th scope="col" class="table-head">Year</th>
                                        <th scope="col" class="table-head">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($histories as $history)
                                        <tr class="table-row">
                                            <td class="table-cell">
                                                <p class="font-semibold text-bright">{{ $history->condition_name }}</p>
                                                <p class="text-xs text-meta">{{ \Illuminate\Support\Str::headline($history->type) }}</p>
                                            </td>
                                            <td class="table-cell cs-num font-mono text-xs">{{ $history->year_diagnosed ?? '—' }}</td>
                                            <td class="table-cell text-xs">{{ $history->status ? \Illuminate\Support\Str::headline($history->status) : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="px-5 py-10 text-center text-xs text-meta">No medical history recorded.</p>
                    @endif
                </div>

                {{-- Tab 3: Previous Consultations --}}
                <div id="emr-panel-visits" role="tabpanel" aria-labelledby="emr-tab-visits" data-tab-panel="visits" hidden>
                    @if ($previousEncounters->isNotEmpty())
                        <div class="divide-y divide-hairline">
                            @foreach ($previousEncounters as $previous)
                                <div class="flex items-start justify-between gap-3 px-5 py-3.5 transition-colors hover:bg-canvas">
                                    <div class="min-w-0">
                                        <p class="text-sm">
                                            <a href="{{ route('physician.encounters.show', $previous) }}" class="font-mono text-xs font-semibold text-brand hover:underline">{{ $previous->encounter_number }}</a>
                                            <span class="ml-2 cs-num font-mono text-xs text-meta">{{ $previous->encountered_at->isoFormat('MMM D, YYYY') }}</span>
                                        </p>
                                        <p class="mt-0.5 truncate text-xs text-read">
                                            {{ $previous->diagnosis }} · Dr. {{ $previous->physician->user->name }}
                                        </p>
                                    </div>
                                    <span class="badge badge-{{ $previous->status }}">{{ \Illuminate\Support\Str::headline($previous->status) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="px-5 py-10 text-center text-xs text-meta">No previous consultations on file.</p>
                    @endif
                </div>
            </div>
        </aside>
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
            normalizeHeight();
        })();
    </script>
@endpush
