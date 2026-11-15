@extends('layouts.app')

@section('title', 'Patient EMR — '.($patient->user?->name ?? $patient->patient_number))

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Encounter log', 'href' => route('physician.encounters.index')],
        ['label' => $patient->user?->name ?? $patient->patient_number],
    ]" />

    <x-page-header
        :title="$patient->user?->name ?? 'Patient chart'"
        description="Complete outpatient medical record, allergies, consultation history, and appointments.">
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route('physician.dashboard') }}">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Back to queue
            </a>
        </x-slot:actions>
    </x-page-header>

    @php
        $activeAllergies = $allergies->where('is_active', true);
    @endphp

    @if ($activeAllergies->isNotEmpty())
        <x-alert type="error" class="mb-6" title="Active allergies ({{ $activeAllergies->count() }})">
            <ul class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                @foreach ($activeAllergies as $allergy)
                    <li>
                        <strong class="font-semibold">{{ $allergy->allergen }}</strong>
                        ({{ \Illuminate\Support\Str::headline($allergy->severity) }} — {{ $allergy->reaction }})
                    </li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    {{-- Clinical identity ledger --}}
    <section class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <x-card-title>Patient profile</x-card-title>
            <span class="cs-num font-mono text-xs font-semibold text-[#0F766E]">
                Record #{{ $patient->patient_number }}
            </span>
        </div>
        <div class="card-body p-0">
            <dl class="grid grid-cols-2 divide-y divide-[#DCE3E1] sm:grid-cols-4 sm:divide-x">
                <div class="px-5 py-3.5">
                    <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Age / Sex</dt>
                    <dd class="cs-num mt-1 text-sm font-medium text-[#14201E]">
                        {{ $patient->age !== null ? $patient->age.' yrs' : '—' }} · {{ $patient->sex ? \Illuminate\Support\Str::headline($patient->sex) : '—' }}
                    </dd>
                </div>
                <div class="px-5 py-3.5">
                    <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Date of birth</dt>
                    <dd class="cs-num mt-1 text-sm font-medium text-[#14201E]">
                        {{ $patient->date_of_birth?->isoFormat('MMM D, YYYY') ?? '—' }}
                    </dd>
                </div>
                <div class="px-5 py-3.5">
                    <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Blood type</dt>
                    <dd class="mt-1 text-sm font-semibold text-[#14201E]">
                        {{ $patient->blood_type ?: '—' }}
                    </dd>
                </div>
                <div class="px-5 py-3.5">
                    <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Civil status</dt>
                    <dd class="mt-1 text-sm font-medium text-[#14201E]">
                        {{ $patient->civil_status ? \Illuminate\Support\Str::headline($patient->civil_status) : '—' }}
                    </dd>
                </div>
                <div class="col-span-2 px-5 py-3.5">
                    <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Address</dt>
                    <dd class="mt-1 text-sm text-[#14201E]">{{ $patient->address ?: 'Not recorded' }}</dd>
                </div>
                <div class="col-span-2 px-5 py-3.5">
                    <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Emergency contact</dt>
                    <dd class="cs-num mt-1 text-sm text-[#14201E]">
                        @if ($patient->emergency_contact_name)
                            {{ $patient->emergency_contact_name }}
                            @if ($patient->emergency_contact_relationship)
                                ({{ $patient->emergency_contact_relationship }})
                            @endif
                            @if ($patient->emergency_contact_phone)
                                · {{ $patient->emergency_contact_phone }}
                            @endif
                        @else
                            Not recorded
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- Allergies & Medical history --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="card">
            <div class="card-header flex items-center justify-between gap-2">
                <x-card-title>Allergy list</x-card-title>
                <span class="cs-num text-xs text-[#465854]">{{ $allergies->count() }} recorded</span>
            </div>
            <div class="card-body p-0">
                @if ($allergies->isNotEmpty())
                    <ul class="divide-y divide-[#DCE3E1]">
                        @foreach ($allergies as $allergy)
                            <li class="flex items-start justify-between gap-3 px-5 py-3.5 {{ $allergy->is_active ? '' : 'bg-[#F4F7F6]/70' }}">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium {{ $allergy->is_active ? 'text-[#14201E]' : 'text-[#465854]' }}">
                                        {{ $allergy->allergen }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-[#465854]">{{ $allergy->reaction ?: 'Reaction not specified' }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <span class="badge badge-{{ $allergy->severity }}">
                                        {{ \Illuminate\Support\Str::headline($allergy->severity) }}
                                    </span>
                                    <span class="badge badge-{{ $allergy->is_active ? 'enabled' : 'inactive' }}">
                                        {{ $allergy->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="p-6">
                        <x-empty icon="heart-pulse" title="No allergies documented."
                                 description="Reported medication or food sensitivities appear here." />
                    </div>
                @endif
            </div>
        </section>

        <section class="card">
            <div class="card-header flex items-center justify-between gap-2">
                <x-card-title>Medical history</x-card-title>
                <span class="cs-num text-xs text-[#465854]">{{ $histories->count() }} {{ \Illuminate\Support\Str::plural('condition', $histories->count()) }}</span>
            </div>
            <div class="card-body p-0">
                @if ($histories->isNotEmpty())
                    <ul class="divide-y divide-[#DCE3E1]">
                        @foreach ($histories as $history)
                            <li class="px-5 py-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-[#14201E]">{{ $history->condition_name }}</p>
                                        <p class="cs-num mt-0.5 text-xs text-[#465854]">
                                            {{ \Illuminate\Support\Str::headline($history->type) }}
                                            @if ($history->year_diagnosed)
                                                · Diagnosed {{ $history->year_diagnosed }}
                                            @endif
                                        </p>
                                        @if ($history->notes)
                                            <p class="mt-1 text-xs text-[#465854]">{{ $history->notes }}</p>
                                        @endif
                                    </div>
                                    <span class="badge badge-{{ $history->status ?: 'inactive' }} shrink-0">
                                        {{ \Illuminate\Support\Str::headline($history->status ?? 'unknown') }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="p-6">
                        <x-empty icon="clipboard" title="No medical history recorded."
                                 description="Chronic conditions, procedures, and family history appear here." />
                    </div>
                @endif
            </div>
        </section>
    </div>

    {{-- Consultation history --}}
    <section class="card mt-6">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>Consultation history</x-card-title>
            <span class="cs-num text-xs text-[#465854]">{{ $encounters->count() }} {{ \Illuminate\Support\Str::plural('visit', $encounters->count()) }}</span>
        </div>
        <div class="card-body p-0">
            @if ($encounters->isNotEmpty())
                <div class="divide-y divide-[#DCE3E1]">
                    @foreach ($encounters as $encounter)
                        <article class="space-y-3 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a class="cs-num font-mono text-xs font-semibold text-[#0F766E] hover:underline"
                                           href="{{ route('physician.encounters.show', $encounter) }}">
                                            {{ $encounter->encounter_number }}
                                        </a>
                                        <span class="badge badge-{{ $encounter->status }}">
                                            {{ \Illuminate\Support\Str::headline($encounter->status) }}
                                        </span>
                                        <span class="cs-num text-xs text-[#465854]">
                                            {{ $encounter->encountered_at?->isoFormat('MMM D, YYYY') ?? '—' }}
                                        </span>
                                    </div>
                                    <h3 class="mt-1 text-sm font-semibold text-[#14201E]">
                                        {{ $encounter->diagnosis ?: 'Diagnosis pending' }}
                                        @if ($encounter->diagnosis_code)
                                            <span class="cs-num ml-1 font-mono text-xs font-normal text-[#465854]">({{ $encounter->diagnosis_code }})</span>
                                        @endif
                                    </h3>
                                    <p class="mt-0.5 text-xs text-[#465854]">
                                        Dr. {{ $encounter->physician?->user?->name ?? '—' }}
                                    </p>
                                </div>
                                <a class="btn btn-secondary btn-sm" href="{{ route('physician.encounters.show', $encounter) }}">
                                    Open chart
                                </a>
                            </div>

                            @if ($encounter->assessment || $encounter->treatment_plan || $encounter->clinical_notes)
                                <dl class="grid gap-2 text-xs sm:grid-cols-3">
                                    @if ($encounter->assessment)
                                        <div class="rounded border border-[#DCE3E1] bg-[#F4F7F6]/70 px-3 py-2">
                                            <dt class="font-semibold text-[#465854] uppercase">Assessment</dt>
                                            <dd class="mt-0.5 text-[#14201E]">{{ $encounter->assessment }}</dd>
                                        </div>
                                    @endif
                                    @if ($encounter->treatment_plan)
                                        <div class="rounded border border-[#DCE3E1] bg-[#F4F7F6]/70 px-3 py-2">
                                            <dt class="font-semibold text-[#465854] uppercase">Treatment plan</dt>
                                            <dd class="mt-0.5 text-[#14201E]">{{ $encounter->treatment_plan }}</dd>
                                        </div>
                                    @endif
                                    @if ($encounter->clinical_notes)
                                        <div class="rounded border border-[#DCE3E1] bg-[#F4F7F6]/70 px-3 py-2">
                                            <dt class="font-semibold text-[#465854] uppercase">Clinical notes</dt>
                                            <dd class="mt-0.5 text-[#14201E]">{{ $encounter->clinical_notes }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            @endif

                            @if ($encounter->vitals)
                                <p class="cs-num font-mono text-xs text-[#465854]">
                                    Vitals:
                                    @if ($encounter->vitals->temperature_c) Temp {{ $encounter->vitals->temperature_c }}°C · @endif
                                    @if ($encounter->vitals->systolic_bp && $encounter->vitals->diastolic_bp) BP {{ $encounter->vitals->systolic_bp }}/{{ $encounter->vitals->diastolic_bp }} · @endif
                                    @if ($encounter->vitals->heart_rate) HR {{ $encounter->vitals->heart_rate }} bpm · @endif
                                    @if ($encounter->vitals->respiratory_rate) RR {{ $encounter->vitals->respiratory_rate }} · @endif
                                    @if ($encounter->vitals->spo2) SpO2 {{ $encounter->vitals->spo2 }}% · @endif
                                    @if ($encounter->vitals->weight_kg) Wt {{ $encounter->vitals->weight_kg }} kg @endif
                                    @if ($encounter->vitals->height_cm) · Ht {{ $encounter->vitals->height_cm }} cm @endif
                                </p>
                            @endif

                            @if ($encounter->prescriptions->isNotEmpty())
                                <ul class="flex flex-wrap gap-2">
                                    @foreach ($encounter->prescriptions as $prescription)
                                        <li class="rounded border border-[#DCE3E1] bg-white px-2.5 py-1 text-xs text-[#14201E]">
                                            <span class="font-medium">{{ $prescription->medication_name }}</span>
                                            <span class="cs-num text-[#465854]">
                                                {{ $prescription->strength }} · {{ $prescription->dosage }} · {{ $prescription->frequency }}
                                                @if ($prescription->duration) · {{ $prescription->duration }} @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="p-6">
                    <x-empty icon="clipboard-check" title="No consultation notes yet."
                             description="Recorded encounters for this patient will appear chronologically." />
                </div>
            @endif
        </div>
    </section>

    {{-- Appointments history --}}
    <section class="card mt-6">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>Appointments history</x-card-title>
            <span class="cs-num text-xs text-[#465854]">Last {{ $appointments->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="divide-y divide-[#DCE3E1] md:hidden">
                @forelse ($appointments as $appointment)
                    <article class="space-y-1.5 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="cs-num text-xs font-medium text-[#14201E]">
                                {{ $appointment->appointment_date->isoFormat('MMM D, YYYY') }} · {{ date('g:i A', strtotime($appointment->start_time)) }}
                            </span>
                            <span class="badge badge-{{ $appointment->status }}">
                                {{ \Illuminate\Support\Str::headline($appointment->status) }}
                            </span>
                        </div>
                        <p class="cs-num text-xs text-[#465854]">
                            Queue #{{ $appointment->queue_number }} · Dr. {{ $appointment->physician?->user?->name ?? '—' }}
                        </p>
                        @if ($appointment->chief_complaint)
                            <p class="text-xs text-[#14201E]">{{ $appointment->chief_complaint }}</p>
                        @endif
                    </article>
                @empty
                    <div class="p-6">
                        <x-empty icon="calendar" title="No appointments recorded."
                                 description="Scheduled and walk-in visits will be listed here." />
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full">
                    <caption class="sr-only">Patient appointments history</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="table-head">Date</th>
                            <th scope="col" class="table-head">Time</th>
                            <th scope="col" class="table-head">Queue #</th>
                            <th scope="col" class="table-head">Physician</th>
                            <th scope="col" class="table-head">Source</th>
                            <th scope="col" class="table-head">Complaint</th>
                            <th scope="col" class="table-head text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#DCE3E1]">
                        @forelse ($appointments as $appointment)
                            <tr class="table-row">
                                <td class="table-cell cs-num whitespace-nowrap text-[#14201E]">
                                    {{ $appointment->appointment_date->isoFormat('MMM D, YYYY') }}
                                </td>
                                <td class="table-cell cs-num whitespace-nowrap text-[#465854]">
                                    {{ date('g:i A', strtotime($appointment->start_time)) }}
                                </td>
                                <td class="table-cell cs-num font-mono font-semibold text-[#0F766E] whitespace-nowrap">
                                    #{{ $appointment->queue_number }}
                                </td>
                                <td class="table-cell whitespace-nowrap text-[#14201E]">
                                    Dr. {{ $appointment->physician?->user?->name ?? '—' }}
                                </td>
                                <td class="table-cell whitespace-nowrap">
                                    <span class="badge badge-{{ $appointment->source }}">
                                        {{ \Illuminate\Support\Str::headline($appointment->source) }}
                                    </span>
                                </td>
                                <td class="table-cell text-[#465854]">
                                    {{ $appointment->chief_complaint ?: '—' }}
                                </td>
                                <td class="table-cell whitespace-nowrap text-right">
                                    <span class="badge badge-{{ $appointment->status }}">
                                        {{ \Illuminate\Support\Str::headline($appointment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-10" colspan="7">
                                    <x-empty icon="calendar" title="No appointments recorded."
                                             description="Scheduled and walk-in visits will be listed here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
