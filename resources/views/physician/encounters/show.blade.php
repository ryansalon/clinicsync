@extends('layouts.app')

@section('title', 'Encounter details')

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Encounter log', 'href' => route('physician.encounters.index')],
        ['label' => 'Encounter '.$encounter->encounter_number],
    ]" />

    <x-page-header title="Encounter details"
                   description="{{ $encounter->encounter_number }} · {{ $encounter->encountered_at->isoFormat('dddd, MMM D, YYYY') }} · {{ $encounter->encountered_at->format('g:i A') }}">
        <x-slot:actions>
            <a href="{{ route('physician.encounters.index') }}" class="btn btn-secondary">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Back to encounter log
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Header summary --}}
    <div class="card">
        <div class="card-body">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="section-title">Encounter #</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $encounter->encounter_number }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ $encounter->encountered_at->isoFormat('MMM D, YYYY') }} · {{ $encounter->encountered_at->format('g:i A') }}
                    </p>
                </div>
                <div>
                    <p class="section-title">Physician</p>
                    <div class="mt-1.5 flex items-center gap-3">
                        <x-avatar :name="$encounter->physician->user->name" size="md" />
                        <p class="min-w-0 text-sm font-semibold text-slate-900">{{ $encounter->physician->user->name }}</p>
                    </div>
                </div>
                <div>
                    <p class="section-title">Patient</p>
                    <div class="mt-1.5 flex items-center gap-3">
                        <x-avatar :name="$encounter->patient->user->name" size="md" />
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900">{{ $encounter->patient->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $encounter->patient->patient_number }}</p>
                        </div>
                    </div>
                    <a href="{{ route('physician.patients.show', $encounter->patient) }}"
                       class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-teal-600 transition hover:text-teal-700">
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
    <div class="mt-6 card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-slate-900">Clinical record</h3>
            <p class="mt-0.5 text-xs text-slate-500">Diagnosis, assessment and plan for this visit.</p>
        </div>
        <div class="card-body space-y-5">
            <div>
                <p class="section-title">Chief complaint</p>
                @if ($encounter->chief_complaint)
                    <p class="mt-1 text-sm text-slate-700">{{ $encounter->chief_complaint }}</p>
                @else
                    <p class="mt-1 text-sm text-slate-400">Not recorded.</p>
                @endif
            </div>

            <div class="divider"></div>

            <div>
                <p class="section-title">Diagnosis</p>
                @if ($encounter->diagnosis)
                    <p class="mt-1 text-sm font-medium text-slate-900">
                        {{ $encounter->diagnosis }}
                        @if ($encounter->diagnosis_code)
                            <span class="ml-1 align-middle inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-500">{{ $encounter->diagnosis_code }}</span>
                        @endif
                    </p>
                @else
                    <p class="mt-1 text-sm text-slate-400">Not recorded.</p>
                @endif
            </div>

            <div class="divider"></div>

            <div>
                <p class="section-title">Assessment</p>
                @if ($encounter->assessment)
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $encounter->assessment }}</p>
                @else
                    <p class="mt-1 text-sm text-slate-400">Not recorded.</p>
                @endif
            </div>

            <div class="divider"></div>

            <div>
                <p class="section-title">Treatment plan</p>
                @if ($encounter->treatment_plan)
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $encounter->treatment_plan }}</p>
                @else
                    <p class="mt-1 text-sm text-slate-400">Not recorded.</p>
                @endif
            </div>

            <div class="divider"></div>

            <div>
                <p class="section-title">Clinical notes</p>
                @if ($encounter->clinical_notes)
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $encounter->clinical_notes }}</p>
                @else
                    <p class="mt-1 text-sm text-slate-400">Not recorded.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Vitals --}}
    <div class="mt-6 card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Vitals</h3>
                <p class="mt-0.5 text-xs text-slate-500">Recorded during this encounter.</p>
            </div>
            <span class="chip">
                <x-icon name="heart-pulse" class="h-4 w-4 text-slate-400" />
                {{ $encounter->vitals !== null ? 'Recorded' : 'Not recorded' }}
            </span>
        </div>
        <div class="card-body">
            @if ($encounter->vitals === null)
                <x-empty icon="heart-pulse" title="No vitals recorded"
                         description="Vitals were not captured during this encounter." />
            @else
                @php($vitals = $encounter->vitals)
                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <dt class="section-title">Temperature</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $vitals->temperature_c ?? '—' }}<span class="ml-1 text-xs font-normal text-slate-500">°C</span>
                        </dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <dt class="section-title">Blood pressure</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $vitals->systolic_bp && $vitals->diastolic_bp ? $vitals->systolic_bp.'/'.$vitals->diastolic_bp : '—' }}<span class="ml-1 text-xs font-normal text-slate-500">mmHg</span>
                        </dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <dt class="section-title">Heart rate</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $vitals->heart_rate ?? '—' }}<span class="ml-1 text-xs font-normal text-slate-500">bpm</span>
                        </dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <dt class="section-title">Respiratory rate</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $vitals->respiratory_rate ?? '—' }}<span class="ml-1 text-xs font-normal text-slate-500">/min</span>
                        </dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <dt class="section-title">Weight</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $vitals->weight_kg ?? '—' }}<span class="ml-1 text-xs font-normal text-slate-500">kg</span>
                        </dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <dt class="section-title">Height</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $vitals->height_cm ?? '—' }}<span class="ml-1 text-xs font-normal text-slate-500">cm</span>
                            @if (! empty($vitals->height_cm))
                                @php($totalInches = (int) round($vitals->height_cm / 2.54))
                                <span class="ml-1 text-xs font-normal text-slate-400">
                                    ({{ intdiv($totalInches, 12) }}&#8242;{{ $totalInches % 12 }}&#8243;)
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <dt class="section-title">SpO₂</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $vitals->spo2 ?? '—' }}<span class="ml-1 text-xs font-normal text-slate-500">%</span>
                        </dd>
                    </div>
                    @if ($vitals->bmi !== null)
                        <div class="rounded-lg border border-teal-200 bg-teal-50 p-3">
                            <dt class="section-title text-teal-700">BMI</dt>
                            <dd class="mt-1 text-sm font-semibold text-teal-800">{{ number_format($vitals->bmi, 1) }}</dd>
                        </div>
                    @endif
                </dl>
            @endif
        </div>
    </div>

    {{-- Prescriptions --}}
    <div class="mt-6 card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Prescriptions</h3>
                <p class="mt-0.5 text-xs text-slate-500">Medications issued for this encounter.</p>
            </div>
            <span class="chip">
                <x-icon name="document" class="h-4 w-4 text-slate-400" />
                {{ $encounter->prescriptions->count() }} issued
            </span>
        </div>
        <div class="card-body">
            @forelse ($encounter->prescriptions as $prescription)
                <div class="{{ $loop->last ? '' : 'mb-4 border-b border-slate-100 pb-4' }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                                <x-icon name="document" class="h-4 w-4" />
                            </span>
                            {{ $prescription->medication_name }}{{ $prescription->strength ? ' '.$prescription->strength : '' }}
                        </p>
                        <span class="chip">{{ $prescription->prescription_number }}</span>
                    </div>
                    <p class="mt-1.5 text-sm text-slate-600">
                        {{ collect([
                            $prescription->dosage,
                            $prescription->frequency,
                            $prescription->route ? \Illuminate\Support\Str::headline($prescription->route) : null,
                            $prescription->duration,
                        ])->filter()->implode(' · ') ?: '—' }}
                        <span class="text-slate-400">·</span> Qty: {{ $prescription->quantity ?? '—' }}
                    </p>
                    @if ($prescription->instructions)
                        <p class="mt-1 text-sm text-slate-500">{{ $prescription->instructions }}</p>
                    @endif
                </div>
            @empty
                <x-empty icon="clipboard" title="No prescriptions issued"
                         description="Medications issued for this encounter will appear here." />
            @endforelse
        </div>
    </div>

    {{-- Forms --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Issue prescription --}}
        <div class="card self-start">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-slate-900">Issue prescription</h3>
                <p class="mt-0.5 text-xs text-slate-500">Attach a new medication to this encounter.</p>
            </div>

            <form method="POST" action="{{ route('physician.prescriptions.store', $encounter) }}">
                @csrf

                <div class="card-body space-y-4">
                    <p class="section-title">Medication</p>

                    <div>
                        <label for="medication_name" class="label">Medication name <span class="text-rose-500">*</span></label>
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
                               class="input @error('quantity') input-invalid @enderror"
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

                <div class="flex justify-end border-t border-slate-200 px-5 py-4">
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="plus" class="h-4 w-4" />
                        Issue prescription
                    </button>
                </div>
            </form>
        </div>

        {{-- Update encounter --}}
        <div class="card self-start">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-slate-900">Update encounter</h3>
                <p class="mt-0.5 text-xs text-slate-500">Amend the diagnosis, plan or clinical notes.</p>
            </div>

            <form method="POST" action="{{ route('physician.encounters.update', $encounter) }}">
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

                    <div>
                        <label for="diagnosis" class="label">Diagnosis <span class="text-rose-500">*</span></label>
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
                               class="input @error('diagnosis_code') input-invalid @enderror"
                               value="{{ old('diagnosis_code', $encounter->diagnosis_code) }}" placeholder="ICD-10, optional">
                        @error('diagnosis_code')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
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
                        <textarea id="clinical_notes" name="clinical_notes" rows="3"
                                  class="input @error('clinical_notes') input-invalid @enderror">{{ old('clinical_notes', $encounter->clinical_notes) }}</textarea>
                        @error('clinical_notes')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <label for="mark_completed" class="label flex items-center gap-2">
                        <input id="mark_completed" name="mark_completed" type="checkbox" value="1"
                               class="h-4 w-4 shrink-0 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                               @checked(old('mark_completed', $encounter->status === 'completed'))>
                        Mark this encounter as completed and close the appointment
                    </label>
                </div>

                <div class="flex justify-end border-t border-slate-200 px-5 py-4">
                    <button type="submit" class="btn btn-secondary">
                        <x-icon name="check" class="h-4 w-4" />
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
