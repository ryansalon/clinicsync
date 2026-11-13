@extends('layouts.app')

@section('title', 'My medical records')

@section('content')
    <x-page-header title="My medical records" description="A read-only copy of the information the clinic keeps about your care." />

    {{-- Sticky in-page navigation --}}
    <nav aria-label="Record sections" class="sticky top-0 z-20 -mx-4 -mt-2 mb-6 flex items-center gap-2 overflow-x-auto bg-slate-50/95 px-4 py-2.5 backdrop-blur sm:-mx-6 sm:px-6">
        <a href="#patient-info" class="chip hover:border-teal-400 hover:text-teal-800 shrink-0">
            <x-icon name="user" class="h-3.5 w-3.5 text-slate-400" />
            Patient info
        </a>
        <a href="#medical-history" class="chip hover:border-teal-400 hover:text-teal-800 shrink-0">
            <x-icon name="clipboard" class="h-3.5 w-3.5 text-slate-400" />
            Medical history ({{ $histories->count() }})
        </a>
        <a href="#allergies" class="chip hover:border-teal-400 hover:text-teal-800 shrink-0">
            <x-icon name="exclamation-triangle" class="h-3.5 w-3.5 text-slate-400" />
            Allergies ({{ $allergies->count() }})
        </a>
        <a href="#consultations" class="chip hover:border-teal-400 hover:text-teal-800 shrink-0">
            <x-icon name="clipboard-check" class="h-3.5 w-3.5 text-slate-400" />
            Consultations ({{ $encounters->count() }})
        </a>
        <a href="#prescriptions" class="chip hover:border-teal-400 hover:text-teal-800 shrink-0">
            <x-icon name="document" class="h-3.5 w-3.5 text-slate-400" />
            Prescriptions ({{ $prescriptions->count() }})
        </a>
    </nav>

    {{-- Record summary --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Medical history" value="{{ $histories->count() }}" icon="clipboard" tone="sky" />
        <x-stat label="Allergies" value="{{ $allergies->count() }}" icon="exclamation-triangle" tone="rose" />
        <x-stat label="Consultations" value="{{ $encounters->count() }}" icon="clipboard-check" tone="teal" />
        <x-stat label="Prescriptions" value="{{ $prescriptions->count() }}" icon="document" tone="violet" />
    </div>

    {{-- Patient demographics --}}
    <section id="patient-info" class="card mt-6 scroll-mt-16">
        <div class="card-header">
            <x-card-title>Patient information</x-card-title>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Patient number</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $patient->patient_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Age</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $patient->age ?? '—' }}{{ $patient->age !== null ? ' years old' : '' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Date of birth</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $patient->date_of_birth?->isoFormat('MMMM D, YYYY') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Sex</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $patient->sex ? \Illuminate\Support\Str::headline($patient->sex) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Civil status</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $patient->civil_status ? \Illuminate\Support\Str::headline($patient->civil_status) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Blood type</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $patient->blood_type ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Address</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $patient->address ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Emergency contact</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $patient->emergency_contact_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Contact number</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $patient->emergency_contact_phone ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Relationship</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $patient->emergency_contact_relationship ?: '—' }}</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- Medical history --}}
    <section id="medical-history" class="card mt-6 scroll-mt-16">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>Medical history</x-card-title>
            <span class="text-xs text-slate-500">{{ $histories->count() }} condition{{ $histories->count() === 1 ? '' : 's' }}</span>
        </div>
        <div class="card-body p-0">
            {{-- Mobile card view (below md) --}}
            <div class="divide-y divide-slate-100 md:hidden">
                @forelse ($histories as $history)
                    <article class="p-4 space-y-1.5">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-medium text-slate-900 text-sm">{{ $history->condition_name }}</p>
                            <span class="badge badge-{{ $history->status ?: 'inactive' }}">
                                {{ \Illuminate\Support\Str::headline($history->status ?? 'unknown') }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">
                            {{ \Illuminate\Support\Str::headline($history->type) }}
                            @if ($history->year_diagnosed)
                                · Diagnosed in {{ $history->year_diagnosed }}
                            @endif
                        </p>
                    </article>
                @empty
                    <div class="p-6">
                        <x-empty icon="clipboard" title="No medical history recorded yet."
                                 description="Conditions you have shared with the clinic will be listed here." />
                    </div>
                @endforelse
            </div>

            {{-- Desktop table (md and up) --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="table-head">Condition</th>
                            <th class="table-head">Type</th>
                            <th class="table-head">Year diagnosed</th>
                            <th class="table-head text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($histories as $history)
                            <tr class="table-row">
                                <td class="table-cell font-medium text-slate-900">{{ $history->condition_name }}</td>
                                <td class="table-cell">{{ \Illuminate\Support\Str::headline($history->type) }}</td>
                                <td class="table-cell">{{ $history->year_diagnosed ?? '—' }}</td>
                                <td class="table-cell text-right">
                                    <span class="badge badge-{{ $history->status ?: 'inactive' }}">
                                        {{ \Illuminate\Support\Str::headline($history->status ?? 'unknown') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-10" colspan="4">
                                    <x-empty icon="clipboard" title="No medical history recorded yet."
                                             description="Conditions you have shared with the clinic will be listed here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Allergies --}}
    <section id="allergies" class="card mt-6 scroll-mt-16">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>Allergies</x-card-title>
            <span class="text-xs text-slate-500">{{ $allergies->count() }} on file</span>
        </div>
        <div class="card-body p-0">
            {{-- Mobile card view (below md) --}}
            <div class="divide-y divide-slate-100 md:hidden">
                @forelse ($allergies as $allergy)
                    <article class="p-4 space-y-1.5 {{ $allergy->is_active ? '' : 'bg-slate-50/60' }}">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-medium text-sm {{ $allergy->is_active ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ $allergy->allergen }}
                            </p>
                            <div class="flex items-center gap-1.5">
                                <span class="badge badge-{{ $allergy->severity }}">
                                    {{ \Illuminate\Support\Str::headline($allergy->severity) }}
                                </span>
                                <span class="badge badge-{{ $allergy->is_active ? 'enabled' : 'inactive' }}">
                                    {{ $allergy->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <p class="text-xs {{ $allergy->is_active ? 'text-slate-600' : 'text-slate-400' }}">
                            {{ $allergy->reaction }}
                        </p>
                    </article>
                @empty
                    <div class="p-6">
                        <x-empty icon="heart-pulse" title="No allergies recorded."
                                 description="Allergies you report to the clinic will be tracked here." />
                    </div>
                @endforelse
            </div>

            {{-- Desktop table (md and up) --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="table-head">Allergen</th>
                            <th class="table-head">Reaction</th>
                            <th class="table-head">Severity</th>
                            <th class="table-head text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($allergies as $allergy)
                            <tr class="table-row {{ $allergy->is_active ? '' : 'bg-slate-50/60' }}">
                                <td class="table-cell {{ $allergy->is_active ? 'font-medium text-slate-900' : 'text-slate-400' }}">
                                    {{ $allergy->allergen }}
                                </td>
                                <td class="table-cell {{ $allergy->is_active ? 'text-slate-700' : 'text-slate-400' }}">
                                    {{ $allergy->reaction }}
                                </td>
                                <td class="table-cell">
                                    <span class="badge badge-{{ $allergy->severity }}">
                                        {{ \Illuminate\Support\Str::headline($allergy->severity) }}
                                    </span>
                                </td>
                                <td class="table-cell text-right">
                                    <span class="badge badge-{{ $allergy->is_active ? 'enabled' : 'inactive' }}">
                                        {{ $allergy->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-10" colspan="4">
                                    <x-empty icon="heart-pulse" title="No allergies recorded."
                                             description="Allergies you report to the clinic will be tracked here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Consultation history --}}
    <section id="consultations" class="card mt-6 scroll-mt-16">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>Consultation history</x-card-title>
            <span class="text-xs text-slate-500">{{ $encounters->count() }} encounter{{ $encounters->count() === 1 ? '' : 's' }}</span>
        </div>
        <div class="card-body space-y-4">
            @forelse ($encounters as $encounter)
                <article class="rounded-lg border border-slate-200 p-4 sm:p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $encounter->diagnosis ?: 'Diagnosis not yet recorded' }}
                                @if ($encounter->diagnosis_code)
                                    <span class="ml-1 text-xs font-normal text-slate-500">(Code {{ $encounter->diagnosis_code }})</span>
                                @endif
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $encounter->encounter_number }}
                                · {{ $encounter->encountered_at->isoFormat('dddd, MMM D, YYYY') }}
                                · Dr. {{ $encounter->physician?->user?->name ?? '—' }}
                            </p>
                        </div>
                        <span class="badge badge-{{ $encounter->status }}">
                            {{ \Illuminate\Support\Str::headline($encounter->status) }}
                        </span>
                    </div>

                    @if ($encounter->chief_complaint)
                        <p class="mt-3 text-sm text-slate-600"><span class="font-medium text-slate-700">Chief complaint:</span> {{ $encounter->chief_complaint }}</p>
                    @endif
                    @if ($encounter->assessment)
                        <p class="mt-2 text-sm text-slate-600"><span class="font-medium text-slate-700">Assessment:</span> {{ $encounter->assessment }}</p>
                    @endif
                    @if ($encounter->treatment_plan)
                        <p class="mt-2 text-sm text-slate-600"><span class="font-medium text-slate-700">Treatment plan:</span> {{ $encounter->treatment_plan }}</p>
                    @endif
                    @if ($encounter->clinical_notes)
                        <p class="mt-2 text-sm text-slate-600"><span class="font-medium text-slate-700">Notes:</span> {{ $encounter->clinical_notes }}</p>
                    @endif

                    @isset ($encounter->vitals)
                        <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <p class="section-title mb-2">Vital signs</p>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div>
                                    <p class="text-xs text-slate-500">Temperature</p>
                                    <p class="text-sm font-medium text-slate-900">{{ $encounter->vitals->temperature_c ?? '—' }} °C</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Blood pressure</p>
                                    <p class="text-sm font-medium text-slate-900">
                                        @if ($encounter->vitals->systolic_bp && $encounter->vitals->diastolic_bp)
                                            {{ $encounter->vitals->systolic_bp }}/{{ $encounter->vitals->diastolic_bp }} mmHg
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Heart rate</p>
                                    <p class="text-sm font-medium text-slate-900">{{ $encounter->vitals->heart_rate ?? '—' }} bpm</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Respiratory rate</p>
                                    <p class="text-sm font-medium text-slate-900">{{ $encounter->vitals->respiratory_rate ?? '—' }} /min</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Weight</p>
                                    <p class="text-sm font-medium text-slate-900">{{ $encounter->vitals->weight_kg ?? '—' }} kg</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Height</p>
                                    <p class="text-sm font-medium text-slate-900">{{ $encounter->vitals->height_cm ?? '—' }} cm</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">SpO₂</p>
                                    <p class="text-sm font-medium text-slate-900">{{ $encounter->vitals->spo2 ?? '—' }} %</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">BMI</p>
                                    <p class="text-sm font-medium text-slate-900">{{ $encounter->vitals->bmi ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    @endisset

                    @if ($encounter->prescriptions?->isNotEmpty())
                        <div class="mt-4">
                            <p class="section-title mb-2">Prescriptions issued</p>
                            <ul class="divide-y divide-slate-100 rounded-lg border border-slate-200">
                                @foreach ($encounter->prescriptions as $prescription)
                                    <li class="px-3 py-2 text-sm">
                                        <span class="font-medium text-slate-900">{{ $prescription->medication_name }}</span>
                                        @if ($prescription->strength)
                                            <span class="text-slate-600">{{ $prescription->strength }}</span>
                                        @endif
                                        <span class="text-slate-500"> — {{ $prescription->dosage }}, {{ $prescription->frequency }}, {{ $prescription->duration }}</span>
                                        @if ($prescription->instructions)
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $prescription->instructions }}</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </article>
            @empty
                <x-empty icon="clipboard" title="No consultations recorded yet."
                         description="Notes, vitals and prescriptions from your clinic visits will appear here." />
            @endforelse
        </div>
    </section>

    {{-- E-prescriptions --}}
    <section id="prescriptions" class="card mt-6 scroll-mt-16">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>E-prescriptions</x-card-title>
            <span class="text-xs text-slate-500">{{ $prescriptions->count() }} issued</span>
        </div>
        <div class="card-body p-0">
            {{-- Mobile card view (below md) --}}
            <div class="divide-y divide-slate-100 md:hidden">
                @forelse ($prescriptions as $prescription)
                    <article class="p-4 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-xs font-semibold text-slate-900">{{ $prescription->prescription_number }}</span>
                            <span class="text-xs text-slate-500">{{ $prescription->issued_at?->isoFormat('MMM D, YYYY') ?? '—' }}</span>
                        </div>
                        <p class="text-sm font-medium text-slate-900">
                            {{ $prescription->medication_name }}
                            @if ($prescription->strength)
                                <span class="text-slate-600">{{ $prescription->strength }}</span>
                            @endif
                            @if ($prescription->dosage_form)
                                <span class="text-xs text-slate-500">({{ \Illuminate\Support\Str::headline($prescription->dosage_form) }})</span>
                            @endif
                        </p>
                        <p class="text-xs text-slate-600">
                            {{ $prescription->dosage }}, {{ $prescription->frequency }}, {{ $prescription->route }}, {{ $prescription->duration }}
                            @if ($prescription->quantity)
                                · Qty: {{ $prescription->quantity }}
                            @endif
                        </p>
                        @if ($prescription->instructions)
                            <p class="text-xs text-slate-500">{{ $prescription->instructions }}</p>
                        @endif
                        <p class="text-xs text-slate-500 pt-1">
                            Dr. {{ $prescription->physician?->user?->name ?? '—' }}
                        </p>
                    </article>
                @empty
                    <div class="p-6">
                        <x-empty icon="document" title="No prescriptions issued yet."
                                 description="Prescriptions sent to the pharmacy will be listed here." />
                    </div>
                @endforelse
            </div>

            {{-- Desktop table (md and up) --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="table-head">Rx number</th>
                            <th class="table-head">Medication</th>
                            <th class="table-head">Directions</th>
                            <th class="table-head">Issued</th>
                            <th class="table-head">Physician</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($prescriptions as $prescription)
                            <tr class="table-row">
                                <td class="table-cell whitespace-nowrap font-medium text-slate-900">{{ $prescription->prescription_number }}</td>
                                <td class="table-cell">
                                    <span class="font-medium text-slate-900">{{ $prescription->medication_name }}</span>
                                    @if ($prescription->strength)
                                        <span class="text-slate-600">{{ $prescription->strength }}</span>
                                    @endif
                                    @if ($prescription->dosage_form)
                                        <span class="block text-xs text-slate-500">{{ \Illuminate\Support\Str::headline($prescription->dosage_form) }}</span>
                                    @endif
                                </td>
                                <td class="table-cell">
                                    {{ $prescription->dosage }}, {{ $prescription->frequency }}, {{ $prescription->route }}, {{ $prescription->duration }}
                                    @if ($prescription->quantity)
                                        <span class="block text-xs text-slate-500">Qty: {{ $prescription->quantity }}</span>
                                    @endif
                                    @if ($prescription->instructions)
                                        <span class="block text-xs text-slate-500">{{ $prescription->instructions }}</span>
                                    @endif
                                </td>
                                <td class="table-cell whitespace-nowrap">{{ $prescription->issued_at?->isoFormat('MMM D, YYYY') ?? '—' }}</td>
                                <td class="table-cell whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if ($prescription->physician?->user?->name)
                                            <x-avatar :name="$prescription->physician->user->name" size="sm" />
                                        @endif
                                        <span>Dr. {{ $prescription->physician?->user?->name ?? '—' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-10" colspan="5">
                                    <x-empty icon="document" title="No prescriptions issued yet."
                                             description="Prescriptions sent to the pharmacy will be listed here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
