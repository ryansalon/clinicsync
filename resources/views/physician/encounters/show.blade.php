@extends('layouts.app')

@section('title', 'Encounter details')

@php
    $vitals = $encounter->vitals;
    $totalInches = ! empty($vitals?->height_cm) ? (int) round($vitals->height_cm / 2.54) : null;
@endphp

@section('content')
    <div class="print-only mb-5 border-b border-hairline pb-3">
        <p class="font-mono text-[10px] font-semibold tracking-wider text-meta uppercase">Camiguin Polytechnic State College · Outpatient Clinic</p>
        <p class="mt-0.5 text-base font-bold text-bright">Outpatient Clinical Summary &amp; E-Prescription — {{ $encounter->encounter_number }}</p>
    </div>

    <x-breadcrumb class="no-print" :items="[
        ['label' => 'Encounter log', 'href' => route('physician.encounters.index')],
        ['label' => 'Encounter '.$encounter->encounter_number],
    ]" />

    <x-page-header title="Encounter details"
                   description="{{ $encounter->encounter_number }} · {{ $encounter->encountered_at->isoFormat('dddd, MMM D, YYYY') }} · {{ $encounter->encountered_at->format('g:i A') }}">
        <x-slot:actions>
            <button type="button" class="btn btn-secondary no-print" onclick="window.print()">
                <x-icon name="document" class="h-4 w-4" />
                Print
            </button>
            <a href="{{ route('physician.encounters.index') }}" class="btn btn-secondary no-print">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Back to encounter log
            </a>
        </x-slot:actions>
    </x-page-header>

    @if ($encounter->patient->activeAllergies->isNotEmpty())
        <x-alert type="error" class="mb-5" title="Active allergies on file ({{ $encounter->patient->activeAllergies->count() }})">
            <ul class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                @foreach ($encounter->patient->activeAllergies as $allergy)
                    <li>
                        <span class="font-semibold">{{ $allergy->allergen }}</span>
                        ({{ \Illuminate\Support\Str::headline($allergy->severity) }}@if ($allergy->reaction) — {{ $allergy->reaction }}@endif)
                    </li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    {{-- Header summary --}}
    <div class="card">
        <div class="card-body">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="section-title">Encounter #</p>
                    <p class="mt-1 font-mono text-sm font-semibold text-brand">{{ $encounter->encounter_number }}</p>
                    <p class="mt-0.5 cs-num font-mono text-xs text-meta">
                        {{ $encounter->encountered_at->isoFormat('MMM D, YYYY') }} · {{ $encounter->encountered_at->format('g:i A') }}
                    </p>
                </div>
                <div>
                    <p class="section-title">Physician</p>
                    <div class="mt-1.5 flex items-center gap-3">
                        <x-avatar :name="$encounter->physician->user->name" size="md" />
                        <p class="min-w-0 text-sm font-semibold text-bright">{{ $encounter->physician->user->name }}</p>
                    </div>
                </div>
                <div>
                    <p class="section-title">Patient</p>
                    <div class="mt-1.5 flex items-center gap-3">
                        <x-avatar :name="$encounter->patient->user->name" size="md" />
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-bright">{{ $encounter->patient->user->name }}</p>
                            <p class="font-mono text-xs text-meta">{{ $encounter->patient->patient_number }}</p>
                        </div>
                    </div>
                    <a href="{{ route('physician.patients.show', $encounter->patient) }}"
                       class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-brand hover:underline">
                        View full record
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>
                <div>
                    <p class="section-title">Status</p>
                    <span class="mt-2 badge badge-{{ $encounter->status }}">{{ \Illuminate\Support\Str::headline($encounter->status) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Clinical record --}}
    <div class="card mt-6">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-bright">Clinical record</h2>
            <p class="mt-0.5 text-xs text-meta">Diagnosis, assessment and plan for this visit.</p>
        </div>
        <div class="divide-y divide-hairline px-5">
            <div class="py-4">
                <p class="section-title">Chief complaint</p>
                <p class="mt-1 text-sm {{ $encounter->chief_complaint ? 'text-read' : 'text-meta' }}">
                    {{ $encounter->chief_complaint ?: 'Not recorded' }}
                </p>
            </div>

            <div class="py-4">
                <p class="section-title">Diagnosis</p>
                @if ($encounter->diagnosis)
                    <p class="mt-1 text-sm font-semibold text-bright">
                        {{ $encounter->diagnosis }}
                        @if ($encounter->diagnosis_code)
                            <span class="ml-1 inline-flex items-center rounded border border-hairline bg-surface-subtle px-1.5 py-0.5 align-middle font-mono text-xs font-medium text-brand">{{ $encounter->diagnosis_code }}</span>
                        @endif
                    </p>
                @else
                    <p class="mt-1 text-sm text-meta">Not recorded</p>
                @endif
            </div>

            <div class="py-4">
                <p class="section-title">Assessment</p>
                <p class="mt-1 whitespace-pre-line text-sm {{ $encounter->assessment ? 'text-read' : 'text-meta' }}">
                    {{ $encounter->assessment ?: 'Not recorded' }}
                </p>
            </div>

            <div class="py-4">
                <p class="section-title">Treatment plan</p>
                <p class="mt-1 whitespace-pre-line text-sm {{ $encounter->treatment_plan ? 'text-read' : 'text-meta' }}">
                    {{ $encounter->treatment_plan ?: 'Not recorded' }}
                </p>
            </div>

            <div class="py-4">
                <p class="section-title">Clinical notes</p>
                <p class="mt-1 whitespace-pre-line text-sm {{ $encounter->clinical_notes ? 'text-read' : 'text-meta' }}">
                    {{ $encounter->clinical_notes ?: 'Not recorded' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Flat hairline Vitals grid --}}
    <div class="card mt-6 overflow-hidden">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-bright">Vitals</h2>
                <p class="mt-0.5 text-xs text-meta">Recorded during this encounter.</p>
            </div>
            <span class="chip">
                <x-icon name="heart-pulse" class="h-4 w-4 text-brand" />
                {{ $vitals !== null ? 'Recorded' : 'Not recorded' }}
            </span>
        </div>
        @if ($vitals === null)
            <div class="card-body">
                <x-empty icon="heart-pulse" title="No vitals recorded"
                         description="You can add vitals below using “Update encounter”." />
            </div>
        @else
            <dl class="grid grid-cols-2 divide-x divide-y divide-hairline sm:grid-cols-4">
                <div class="p-4">
                    <dt class="section-title">Temperature</dt>
                    <dd class="mt-1 cs-num font-mono text-sm font-semibold text-bright">
                        {{ $vitals->temperature_c ?? '—' }}<span class="ml-1 text-xs font-normal text-meta">°C</span>
                    </dd>
                </div>
                <div class="p-4">
                    <dt class="section-title">Blood pressure</dt>
                    <dd class="mt-1 cs-num font-mono text-sm font-semibold text-bright">
                        {{ $vitals->systolic_bp && $vitals->diastolic_bp ? $vitals->systolic_bp.'/'.$vitals->diastolic_bp : '—' }}<span class="ml-1 text-xs font-normal text-meta">mmHg</span>
                    </dd>
                </div>
                <div class="p-4">
                    <dt class="section-title">Heart rate</dt>
                    <dd class="mt-1 cs-num font-mono text-sm font-semibold text-bright">
                        {{ $vitals->heart_rate ?? '—' }}<span class="ml-1 text-xs font-normal text-meta">bpm</span>
                    </dd>
                </div>
                <div class="p-4">
                    <dt class="section-title">Respiratory rate</dt>
                    <dd class="mt-1 cs-num font-mono text-sm font-semibold text-bright">
                        {{ $vitals->respiratory_rate ?? '—' }}<span class="ml-1 text-xs font-normal text-meta">/min</span>
                    </dd>
                </div>
                <div class="p-4">
                    <dt class="section-title">Weight</dt>
                    <dd class="mt-1 cs-num font-mono text-sm font-semibold text-bright">
                        {{ $vitals->weight_kg ?? '—' }}<span class="ml-1 text-xs font-normal text-meta">kg</span>
                    </dd>
                </div>
                <div class="p-4">
                    <dt class="section-title">Height</dt>
                    <dd class="mt-1 cs-num font-mono text-sm font-semibold text-bright">
                        {{ $vitals->height_cm ?? '—' }}<span class="ml-1 text-xs font-normal text-meta">cm</span>
                        @if ($totalInches !== null)
                            <span class="ml-1 text-xs font-normal text-meta">
                                ({{ intdiv($totalInches, 12) }}&#8242;{{ $totalInches % 12 }}&#8243;)
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="p-4">
                    <dt class="section-title">SpO₂</dt>
                    <dd class="mt-1 cs-num font-mono text-sm font-semibold text-bright">
                        {{ $vitals->spo2 ?? '—' }}<span class="ml-1 text-xs font-normal text-meta">%</span>
                    </dd>
                </div>
                @if ($vitals->bmi !== null)
                    <div class="bg-brand-tint/65 p-4">
                        <dt class="section-title text-brand">BMI</dt>
                        <dd class="mt-1 cs-num font-mono text-sm font-semibold text-brand">{{ number_format($vitals->bmi, 1) }}</dd>
                    </div>
                @endif
            </dl>
        @endif
    </div>

    {{-- Prescriptions --}}
    <div class="card mt-6">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-bright">Prescriptions</h2>
                <p class="mt-0.5 text-xs text-meta">Medications issued for this encounter.</p>
            </div>
            <span class="chip cs-num font-mono text-[11px]">
                <x-icon name="document" class="h-3.5 w-3.5 text-meta" />
                {{ $encounter->prescriptions->count() }} issued
            </span>
        </div>
        <div class="card-body">
            @forelse ($encounter->prescriptions as $prescription)
                <div class="{{ $loop->last ? '' : 'mb-4 border-b border-hairline pb-4' }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="flex items-center gap-2 text-sm font-semibold text-bright">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-brand-border bg-brand-tint text-brand">
                                <x-icon name="document" class="h-4 w-4" />
                            </span>
                            {{ $prescription->medication_name }}{{ $prescription->strength ? ' '.$prescription->strength : '' }}
                        </p>
                        <span class="chip font-mono text-[11px]">{{ $prescription->prescription_number }}</span>
                    </div>
                    <p class="mt-1.5 cs-num text-sm text-read">
                        {{ collect([
                            $prescription->dosage,
                            $prescription->frequency,
                            $prescription->route ? \Illuminate\Support\Str::headline($prescription->route) : null,
                            $prescription->duration,
                        ])->filter()->implode(' · ') ?: '—' }}
                        <span class="text-meta">·</span> Qty: {{ $prescription->quantity ?? '—' }}
                    </p>
                    @if ($prescription->instructions)
                        <p class="mt-1 text-sm text-meta">{{ $prescription->instructions }}</p>
                    @endif
                </div>
            @empty
                <x-empty icon="clipboard" title="No prescriptions issued"
                         description="Medications issued for this encounter will appear here." />
            @endforelse
        </div>
    </div>

    {{-- Forms --}}
    <div class="no-print mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Issue prescription --}}
        <div class="card self-start">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-bright">Issue prescription</h2>
                <p class="mt-0.5 text-xs text-meta">Attach a medication to this encounter.</p>
            </div>

            <form method="POST" action="{{ route('physician.prescriptions.store', $encounter) }}" data-dirty-guard>
                @csrf

                <div class="card-body space-y-4">
                    <p class="section-title">Medication</p>

                    <div>
                        <label for="medication_name" class="label">Medication name <span class="text-danger">*</span></label>
                        <input id="medication_name" name="medication_name" type="text" required
                               class="input @error('medication_name') input-invalid @enderror"
                               value="{{ old('medication_name') }}" placeholder="e.g. Amoxicillin">
                        @error('medication_name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="divider"></div>
                    <p class="section-title">Dosage &amp; schedule</p>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="strength" class="label">Strength</label>
                            <input id="strength" name="strength" type="text"
                                   class="input @error('strength') input-invalid @enderror"
                                   value="{{ old('strength') }}" placeholder="e.g. 500 mg">
                            @error('strength')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="dosage_form" class="label">Dosage form</label>
                            <input id="dosage_form" name="dosage_form" type="text"
                                   class="input @error('dosage_form') input-invalid @enderror"
                                   value="{{ old('dosage_form') }}" placeholder="e.g. capsule">
                            @error('dosage_form')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="dosage" class="label">Dosage</label>
                            <input id="dosage" name="dosage" type="text"
                                   class="input @error('dosage') input-invalid @enderror"
                                   value="{{ old('dosage') }}" placeholder="e.g. 1 capsule">
                            @error('dosage')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="frequency" class="label">Frequency</label>
                            <input id="frequency" name="frequency" type="text"
                                   class="input @error('frequency') input-invalid @enderror"
                                   value="{{ old('frequency') }}" placeholder="e.g. 3× a day">
                            @error('frequency')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="route" class="label">Route</label>
                            <select id="route" name="route" class="input">
                                @foreach (['oral', 'topical', 'intravenous', 'intramuscular', 'subcutaneous', 'inhalation', 'rectal', 'ophthalmic', 'otic'] as $routeOption)
                                    <option value="{{ $routeOption }}" @selected(old('route', 'oral') === $routeOption)>
                                        {{ \Illuminate\Support\Str::headline($routeOption) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('route')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="duration" class="label">Duration</label>
                            <input id="duration" name="duration" type="text"
                                   class="input @error('duration') input-invalid @enderror"
                                   value="{{ old('duration') }}" placeholder="e.g. 7 days">
                            @error('duration')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="quantity" class="label">Quantity</label>
                        <input id="quantity" name="quantity" type="number" min="1"
                               class="input cs-num @error('quantity') input-invalid @enderror"
                               value="{{ old('quantity') }}" placeholder="e.g. 21">
                        @error('quantity')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="divider"></div>
                    <p class="section-title">Instructions</p>

                    <div>
                        <label for="instructions" class="label">Instructions</label>
                        <textarea id="instructions" name="instructions" rows="3"
                                  class="input @error('instructions') input-invalid @enderror"
                                  placeholder="e.g. Take after meals. Complete the full course.">{{ old('instructions') }}</textarea>
                        @error('instructions')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end border-t border-hairline px-5 py-4">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="plus" class="h-4 w-4" />
                        Issue prescription
                    </button>
                </div>
            </form>
        </div>

        {{-- Update encounter + vitals amendment --}}
        <div class="card self-start">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-bright">Update encounter &amp; vitals</h2>
                <p class="mt-0.5 text-xs text-meta">Amend the diagnosis, clinical notes, or vitals.</p>
            </div>

            <form method="POST" action="{{ route('physician.encounters.update', $encounter) }}" data-dirty-guard>
                @csrf
                @method('PUT')

                <div class="card-body space-y-4">
                    <p class="section-title">Assessment</p>

                    <div>
                        <label for="chief_complaint" class="label">Chief complaint</label>
                        <input id="chief_complaint" name="chief_complaint" type="text"
                               class="input @error('chief_complaint') input-invalid @enderror"
                               value="{{ old('chief_complaint', $encounter->chief_complaint) }}">
                        @error('chief_complaint')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label for="diagnosis" class="label">Diagnosis <span class="text-danger">*</span></label>
                            <input id="diagnosis" name="diagnosis" type="text" required
                                   class="input @error('diagnosis') input-invalid @enderror"
                                   value="{{ old('diagnosis', $encounter->diagnosis) }}">
                            @error('diagnosis')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="diagnosis_code" class="label">Diagnosis code</label>
                            <input id="diagnosis_code" name="diagnosis_code" type="text"
                                   class="input font-mono @error('diagnosis_code') input-invalid @enderror"
                                   value="{{ old('diagnosis_code', $encounter->diagnosis_code) }}" placeholder="ICD-10">
                            @error('diagnosis_code')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="assessment" class="label">Assessment</label>
                        <textarea id="assessment" name="assessment" rows="3"
                                  class="input @error('assessment') input-invalid @enderror">{{ old('assessment', $encounter->assessment) }}</textarea>
                        @error('assessment')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="divider"></div>
                    <p class="section-title">Plan &amp; notes</p>

                    <div>
                        <label for="treatment_plan" class="label">Treatment plan</label>
                        <textarea id="treatment_plan" name="treatment_plan" rows="3"
                                  class="input @error('treatment_plan') input-invalid @enderror">{{ old('treatment_plan', $encounter->treatment_plan) }}</textarea>
                        @error('treatment_plan')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="clinical_notes" class="label">Clinical notes</label>
                        <textarea id="clinical_notes" name="clinical_notes" rows="2"
                                  class="input @error('clinical_notes') input-invalid @enderror">{{ old('clinical_notes', $encounter->clinical_notes) }}</textarea>
                        @error('clinical_notes')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <fieldset class="border-t border-hairline pt-4">
                        <legend class="section-title">Amend vitals</legend>
                        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <div>
                                <label for="edit_temperature_c" class="label">Temp (°C)</label>
                                <input id="edit_temperature_c" name="temperature_c" type="number" step="0.1"
                                       class="input cs-num" value="{{ old('temperature_c', $vitals?->temperature_c) }}">
                            </div>
                            <div>
                                <label for="edit_systolic_bp" class="label">Systolic</label>
                                <input id="edit_systolic_bp" name="systolic_bp" type="number"
                                       class="input cs-num" value="{{ old('systolic_bp', $vitals?->systolic_bp) }}">
                            </div>
                            <div>
                                <label for="edit_diastolic_bp" class="label">Diastolic</label>
                                <input id="edit_diastolic_bp" name="diastolic_bp" type="number"
                                       class="input cs-num" value="{{ old('diastolic_bp', $vitals?->diastolic_bp) }}">
                            </div>
                            <div>
                                <label for="edit_heart_rate" class="label">HR (bpm)</label>
                                <input id="edit_heart_rate" name="heart_rate" type="number"
                                       class="input cs-num" value="{{ old('heart_rate', $vitals?->heart_rate) }}">
                            </div>
                            <div>
                                <label for="edit_respiratory_rate" class="label">RR (/min)</label>
                                <input id="edit_respiratory_rate" name="respiratory_rate" type="number"
                                       class="input cs-num" value="{{ old('respiratory_rate', $vitals?->respiratory_rate) }}">
                            </div>
                            <div>
                                <label for="edit_spo2" class="label">SpO₂ (%)</label>
                                <input id="edit_spo2" name="spo2" type="number" min="40" max="100"
                                       class="input cs-num" value="{{ old('spo2', $vitals?->spo2) }}">
                            </div>
                            <div>
                                <label for="edit_weight_kg" class="label">Weight (kg)</label>
                                <input id="edit_weight_kg" name="weight_kg" type="number" step="0.1" min="1" max="500"
                                       class="input cs-num" value="{{ old('weight_kg', $vitals?->weight_kg) }}">
                            </div>
                            <div>
                                <label for="edit_height_cm" class="label">Height (cm)</label>
                                <input id="edit_height_cm" name="height_cm" type="number" step="0.1" min="30" max="260"
                                       class="input cs-num" value="{{ old('height_cm', $vitals?->height_cm) }}">
                            </div>
                        </div>
                    </fieldset>

                    <div class="border-t border-hairline pt-4">
                        <label for="mark_completed" class="label mb-0 flex cursor-pointer items-center gap-2">
                            <input id="mark_completed" name="mark_completed" type="checkbox" value="1"
                                   class="h-4 w-4 shrink-0 rounded border-hairline-2 accent-brand"
                                   @checked(old('mark_completed', $encounter->status === 'completed'))>
                            <span>Mark this encounter as completed and close the appointment</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end border-t border-hairline px-5 py-4">
                    <button type="submit" class="btn btn-secondary">
                        <x-icon name="check" class="h-4 w-4" />
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
