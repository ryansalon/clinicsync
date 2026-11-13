@extends('layouts.app')

@section('title', 'Patient record')

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Encounter log', 'href' => route('physician.encounters.index')],
        ['label' => $patient->user->name],
    ]" />

    <x-page-header title="Patient record"
                   description="{{ $patient->user->name }} · {{ $patient->patient_number }}">
        <x-slot:actions>
            <a href="{{ route('physician.encounters.index') }}" class="btn btn-secondary">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Back to encounter log
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Identity --}}
    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-3">
            <x-card-title :description="$patient->patient_number">
                {{ $patient->user->name }}
            </x-card-title>
            <span class="rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700">{{ $patient->patient_number }}</span>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="section-title">Age</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $patient->age ?? '—' }} years old</dd>
                </div>
                <div>
                    <dt class="section-title">Sex</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $patient->sex ? \Illuminate\Support\Str::headline($patient->sex) : '—' }}</dd>
                </div>
                <div>
                    <dt class="section-title">Date of birth</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $patient->date_of_birth?->isoFormat('dddd, MMM D, YYYY') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="section-title">Civil status</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $patient->civil_status ? \Illuminate\Support\Str::headline($patient->civil_status) : '—' }}</dd>
                </div>
                <div>
                    <dt class="section-title">Blood type</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $patient->blood_type ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="section-title">Address</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $patient->address ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="section-title">Emergency contact</dt>
                    <dd class="mt-1 text-sm text-slate-700">
                        {{ $patient->emergency_contact_name ?: '—' }}
                        @if ($patient->emergency_contact_relationship)
                            <span class="text-slate-500">({{ $patient->emergency_contact_relationship }})</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="section-title">Contact number</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $patient->emergency_contact_phone ?: '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Allergies --}}
        <div class="card self-start">
            <div class="card-header flex flex-wrap items-center justify-between gap-2">
                <x-card-title description="Active and inactive entries on file.">Allergies</x-card-title>
                <span class="chip">
                    <x-icon name="exclamation-triangle" class="h-4 w-4 text-slate-400" />
                    {{ $allergies->count() }} on file
                </span>
            </div>
            <div class="card-body">
                @forelse ($allergies as $allergy)
                    <div class="flex flex-wrap items-start justify-between gap-3 {{ $loop->last ? '' : 'mb-4 border-b border-slate-100 pb-4' }}">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900">{{ $allergy->allergen }}</p>
                            <p class="mt-0.5 text-sm text-slate-600">{{ $allergy->reaction }}</p>
                            <p class="mt-1">
                                <span class="badge badge-{{ $allergy->severity }}">
                                    {{ \Illuminate\Support\Str::headline($allergy->severity) }}
                                </span>
                            </p>
                        </div>
                        <span class="badge {{ $allergy->is_active ? 'badge-enabled' : 'badge-inactive' }}">
                            {{ $allergy->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No allergies recorded for this patient.</p>
                @endforelse
            </div>
        </div>

        {{-- Medical history --}}
        <div class="card self-start">
            <div class="card-header">
                <x-card-title description="Documented past conditions.">Medical history</x-card-title>
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
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($histories as $history)
                            <tr>
                                <td class="table-cell">
                                    <p class="font-medium text-slate-900">{{ $history->condition_name }}</p>
                                    @if ($history->notes)
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $history->notes }}</p>
                                    @endif
                                </td>
                                <td class="table-cell whitespace-nowrap">{{ \Illuminate\Support\Str::headline($history->type) }}</td>
                                <td class="table-cell">{{ $history->year_diagnosed ?? '—' }}</td>
                                <td class="table-cell">{{ $history->status ? \Illuminate\Support\Str::headline($history->status) : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">
                                    No medical history recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Consultation history --}}
    <div class="mt-6 card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <x-card-title description="Every recorded encounter, newest first.">Consultation history</x-card-title>
            <span class="chip">
                <x-icon name="clipboard" class="h-4 w-4 text-slate-400" />
                {{ $encounters->count() }} encounters
            </span>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($encounters as $encounter)
                <div class="px-5 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <a href="{{ route('physician.encounters.show', $encounter) }}" class="text-sm font-semibold text-slate-900 hover:text-teal-700">
                                {{ $encounter->encounter_number }}
                            </a>
                            <span class="text-xs text-slate-500">{{ $encounter->encountered_at->isoFormat('dddd, MMM D, YYYY') }}</span>
                            <span class="text-xs text-slate-500">· {{ $encounter->physician->user->name }}</span>
                        </div>
                        <span class="badge badge-{{ $encounter->status }}">{{ \Illuminate\Support\Str::headline($encounter->status) }}</span>
                    </div>

                    <div class="mt-3 space-y-1.5">
                        <p class="text-sm font-medium text-slate-900">
                            {{ $encounter->diagnosis }}
                            @if ($encounter->diagnosis_code)
                                <span class="ml-1 align-middle inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-500">{{ $encounter->diagnosis_code }}</span>
                            @endif
                        </p>

                        @isset($encounter->assessment)
                            <p class="text-sm text-slate-600">
                                <span class="font-medium text-slate-700">Assessment:</span> {{ $encounter->assessment }}
                            </p>
                        @else
                            <p class="text-sm text-slate-400">Assessment: Not recorded.</p>
                        @endisset

                        @isset($encounter->treatment_plan)
                            <p class="text-sm text-slate-600">
                                <span class="font-medium text-slate-700">Treatment plan:</span> {{ $encounter->treatment_plan }}
                            </p>
                        @else
                            <p class="text-sm text-slate-400">Treatment plan: Not recorded.</p>
                        @endisset

                        @isset($encounter->clinical_notes)
                            <p class="text-sm text-slate-600">
                                <span class="font-medium text-slate-700">Clinical notes:</span> {{ $encounter->clinical_notes }}
                            </p>
                        @else
                            <p class="text-sm text-slate-400">Clinical notes: Not recorded.</p>
                        @endisset
                    </div>

                    @isset($encounter->vitals)
                        <p class="mt-3 rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600">
                            <span class="font-semibold text-slate-700">Vitals:</span>
                            {{ $encounter->vitals->temperature_c ?? '—' }} °C
                            · {{ $encounter->vitals->systolic_bp && $encounter->vitals->diastolic_bp ? $encounter->vitals->systolic_bp.'/'.$encounter->vitals->diastolic_bp : '—' }} mmHg
                            · {{ $encounter->vitals->heart_rate ?? '—' }} bpm
                            · {{ $encounter->vitals->respiratory_rate ?? '—' }}/min
                            · SpO₂ {{ $encounter->vitals->spo2 ?? '—' }}%
                            · {{ $encounter->vitals->weight_kg ?? '—' }} kg
                            · {{ $encounter->vitals->height_cm ?? '—' }} cm
                            @if ($encounter->vitals->bmi !== null)
                                · BMI {{ $encounter->vitals->bmi }}
                            @endif
                        </p>
                    @endisset

                    @isset($encounter->prescriptions)
                        @if ($encounter->prescriptions->isNotEmpty())
                            <div class="mt-3">
                                <p class="section-title">Prescriptions</p>
                                <ul class="mt-1 space-y-1">
                                    @foreach ($encounter->prescriptions as $prescription)
                                        <li class="text-xs text-slate-600">
                                            <span class="font-medium text-slate-700">{{ $prescription->prescription_number }}</span>
                                            · {{ $prescription->medication_name }}{{ $prescription->strength ? ' '.$prescription->strength : '' }}
                                            · {{ collect([
                                                $prescription->dosage,
                                                $prescription->frequency,
                                                $prescription->route ? \Illuminate\Support\Str::headline($prescription->route) : null,
                                                $prescription->duration,
                                            ])->filter()->implode(' · ') ?: '—' }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endisset
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-500">No consultations recorded for this patient.</p>
            @endforelse
        </div>
    </div>

    {{-- Appointments --}}
    <div class="mt-6 card">
        <div class="card-header">
            <x-card-title description="Most recent 20 appointments, newest first.">Appointments history</x-card-title>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th class="table-head">Date</th>
                        <th class="table-head">Time</th>
                        <th class="table-head">Physician</th>
                        <th class="table-head">Queue #</th>
                        <th class="table-head">Source</th>
                        <th class="table-head">Status</th>
                        <th class="table-head">Chief complaint</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td class="table-cell whitespace-nowrap">{{ $appointment->appointment_date->isoFormat('dddd, MMM D, YYYY') }}</td>
                            <td class="table-cell whitespace-nowrap">{{ date('g:i A', strtotime($appointment->start_time)) }}</td>
                            <td class="table-cell whitespace-nowrap">{{ $appointment->physician->user->name }}</td>
                            <td class="table-cell">#{{ $appointment->queue_number }}</td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                            </td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                            </td>
                            <td class="table-cell">{{ $appointment->chief_complaint ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                                No appointments recorded for this patient.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
