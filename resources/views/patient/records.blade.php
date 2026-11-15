@extends('layouts.app')

@section('title', 'My medical records')

@section('content')
    <x-page-header title="My medical records" description="Read-only clinical summary of your outpatient chart and prescriptions." />

    {{-- Sticky in-page navigation --}}
    <nav aria-label="Record sections" class="sticky top-0 z-20 -mx-4 -mt-2 mb-6 flex items-center gap-2 overflow-x-auto border-b border-[#DCE3E1] bg-[#F4F7F6]/95 px-4 py-2.5 backdrop-blur sm:-mx-6 sm:px-6">
        <a href="#patient-info" class="chip min-h-11 shrink-0 hover:border-[#0F766E] hover:text-[#0F766E]">
            <x-icon name="user" class="h-3.5 w-3.5 text-[#465854]" />
            Patient info
        </a>
        <a href="#medical-history" class="chip min-h-11 shrink-0 hover:border-[#0F766E] hover:text-[#0F766E]">
            <x-icon name="clipboard" class="h-3.5 w-3.5 text-[#465854]" />
            Medical history ({{ $histories->count() }})
        </a>
        <a href="#allergies" class="chip min-h-11 shrink-0 hover:border-[#0F766E] hover:text-[#0F766E]">
            <x-icon name="exclamation-triangle" class="h-3.5 w-3.5 text-[#465854]" />
            Allergies ({{ $allergies->count() }})
        </a>
        <a href="#consultations" class="chip min-h-11 shrink-0 hover:border-[#0F766E] hover:text-[#0F766E]">
            <x-icon name="clipboard-check" class="h-3.5 w-3.5 text-[#465854]" />
            Consultations ({{ $encounters->total() }})
        </a>
        <a href="#prescriptions" class="chip min-h-11 shrink-0 hover:border-[#0F766E] hover:text-[#0F766E]">
            <x-icon name="document" class="h-3.5 w-3.5 text-[#465854]" />
            Prescriptions ({{ $prescriptions->total() }})
        </a>
    </nav>

    {{-- Record summary --}}
    <dl class="ledger-strip sm:grid-cols-2 lg:grid-cols-4">
        <div class="ledger-cell">
            <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Medical history</dt>
            <dd class="cs-num mt-1 text-2xl font-semibold text-[#14201E]">{{ $histories->count() }}</dd>
        </div>
        <div class="ledger-cell">
            <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Allergies</dt>
            <dd class="cs-num mt-1 text-2xl font-semibold {{ $allergies->where('is_active', true)->isNotEmpty() ? 'text-[#9F1239]' : 'text-[#14201E]' }}">{{ $allergies->count() }}</dd>
        </div>
        <div class="ledger-cell">
            <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Consultations</dt>
            <dd class="cs-num mt-1 text-2xl font-semibold text-[#14201E]">{{ $encounters->total() }}</dd>
        </div>
        <div class="ledger-cell">
            <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Prescriptions</dt>
            <dd class="cs-num mt-1 text-2xl font-semibold text-[#14201E]">{{ $prescriptions->total() }}</dd>
        </div>
    </dl>

    {{-- Patient demographics --}}
    <section id="patient-info" class="card mt-6 scroll-mt-20">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>Patient information</x-card-title>
            <span class="cs-num font-mono text-xs font-semibold text-[#0F766E]">#{{ $patient->patient_number }}</span>
        </div>
        <div class="card-body p-0">
            <dl class="grid grid-cols-1 divide-y divide-[#DCE3E1] sm:grid-cols-3 sm:divide-x">
                <div class="px-5 py-3.5">
                    <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Age / Sex</dt>
                    <dd class="cs-num mt-1 text-sm font-medium text-[#14201E]">
                        {{ $patient->age !== null ? $patient->age.' years old' : '—' }} · {{ $patient->sex ? \Illuminate\Support\Str::headline($patient->sex) : '—' }}
                    </dd>
                </div>
                <div class="px-5 py-3.5">
                    <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Date of birth</dt>
                    <dd class="cs-num mt-1 text-sm font-medium text-[#14201E]">{{ $patient->date_of_birth?->isoFormat('MMMM D, YYYY') ?? '—' }}</dd>
                </div>
                <div class="px-5 py-3.5">
                    <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Blood type · Civil status</dt>
                    <dd class="mt-1 text-sm font-medium text-[#14201E]">
                        {{ $patient->blood_type ?: '—' }} · {{ $patient->civil_status ? \Illuminate\Support\Str::headline($patient->civil_status) : '—' }}
                    </dd>
                </div>
                <div class="px-5 py-3.5 sm:col-span-2">
                    <dt class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Address</dt>
                    <dd class="mt-1 text-sm text-[#14201E]">{{ $patient->address ?: '—' }}</dd>
                </div>
                <div class="px-5 py-3.5">
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
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- Medical history --}}
    <section id="medical-history" class="card mt-6 scroll-mt-20">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>Medical history</x-card-title>
            <span class="cs-num text-xs text-[#465854]">{{ $histories->count() }} {{ \Illuminate\Support\Str::plural('condition', $histories->count()) }}</span>
        </div>
        <div class="card-body p-0">
            <div class="divide-y divide-[#DCE3E1] md:hidden">
                @forelse ($histories as $history)
                    <article class="space-y-1.5 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-medium text-[#14201E]">{{ $history->condition_name }}</p>
                            <span class="badge badge-{{ $history->status ?: 'inactive' }}">
                                {{ \Illuminate\Support\Str::headline($history->status ?? 'unknown') }}
                            </span>
                        </div>
                        <p class="cs-num text-xs text-[#465854]">
                            {{ \Illuminate\Support\Str::headline($history->type) }}
                            @if ($history->year_diagnosed)
                                · Diagnosed {{ $history->year_diagnosed }}
                            @endif
                        </p>
                    </article>
                @empty
                    <div class="p-6">
                        <x-empty icon="clipboard" title="No medical history recorded yet."
                                 description="Conditions shared with the clinic appear here." />
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full">
                    <caption class="sr-only">Recorded patient medical history</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="table-head">Condition</th>
                            <th scope="col" class="table-head">Type</th>
                            <th scope="col" class="table-head">Year diagnosed</th>
                            <th scope="col" class="table-head text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#DCE3E1]">
                        @forelse ($histories as $history)
                            <tr class="table-row">
                                <td class="table-cell font-medium text-[#14201E]">{{ $history->condition_name }}</td>
                                <td class="table-cell text-[#465854]">{{ \Illuminate\Support\Str::headline($history->type) }}</td>
                                <td class="table-cell cs-num text-[#465854]">{{ $history->year_diagnosed ?? '—' }}</td>
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
                                             description="Conditions shared with the clinic appear here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Allergies --}}
    <section id="allergies" class="card mt-6 scroll-mt-20">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>Allergies</x-card-title>
            <span class="cs-num text-xs text-[#465854]">{{ $allergies->count() }} on file</span>
        </div>
        <div class="card-body p-0">
            <div class="divide-y divide-[#DCE3E1] md:hidden">
                @forelse ($allergies as $allergy)
                    <article class="space-y-1.5 p-4 {{ $allergy->is_active ? '' : 'bg-[#F4F7F6]/70' }}">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-medium {{ $allergy->is_active ? 'text-[#14201E]' : 'text-[#465854]' }}">
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
                        <p class="text-xs text-[#465854]">{{ $allergy->reaction }}</p>
                    </article>
                @empty
                    <div class="p-6">
                        <x-empty icon="heart-pulse" title="No allergies recorded."
                                 description="Allergies reported to the clinic appear here." />
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full">
                    <caption class="sr-only">Recorded patient allergies</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="table-head">Allergen</th>
                            <th scope="col" class="table-head">Reaction</th>
                            <th scope="col" class="table-head">Severity</th>
                            <th scope="col" class="table-head text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#DCE3E1]">
                        @forelse ($allergies as $allergy)
                            <tr class="table-row {{ $allergy->is_active ? '' : 'bg-[#F4F7F6]/70' }}">
                                <td class="table-cell {{ $allergy->is_active ? 'font-medium text-[#14201E]' : 'text-[#465854]' }}">
                                    {{ $allergy->allergen }}
                                </td>
                                <td class="table-cell text-[#465854]">{{ $allergy->reaction }}</td>
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
                                             description="Allergies reported to the clinic appear here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Consultation history (flat hairline entries, no nested cards) --}}
    <section id="consultations" class="card mt-6 scroll-mt-20">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>Consultation history</x-card-title>
            <span class="cs-num text-xs text-[#465854]">{{ $encounters->total() }} {{ \Illuminate\Support\Str::plural('encounter', $encounters->total()) }}</span>
        </div>
        <div class="card-body p-0">
            @if ($encounters->isNotEmpty())
                <div class="divide-y divide-[#DCE3E1]">
                    @foreach ($encounters as $encounter)
                        <article class="space-y-3.5 p-5">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-[#14201E]">
                                        {{ $encounter->diagnosis ?: 'Diagnosis not yet recorded' }}
                                        @if ($encounter->diagnosis_code)
                                            <span class="cs-num ml-1 font-mono text-xs font-normal text-[#465854]">({{ $encounter->diagnosis_code }})</span>
                                        @endif
                                    </p>
                                    <p class="cs-num mt-0.5 text-xs text-[#465854]">
                                        <span class="font-mono font-medium text-[#0F766E]">{{ $encounter->encounter_number }}</span>
                                        · {{ $encounter->encountered_at->isoFormat('dddd, MMM D, YYYY') }}
                                        · Dr. {{ $encounter->physician?->user?->name ?? '—' }}
                                    </p>
                                </div>
                                <span class="badge badge-{{ $encounter->status }} shrink-0">
                                    {{ \Illuminate\Support\Str::headline($encounter->status) }}
                                </span>
                            </div>

                            @if ($encounter->chief_complaint || $encounter->assessment || $encounter->treatment_plan || $encounter->clinical_notes)
                                <dl class="space-y-1.5 text-sm text-[#465854]">
                                    @if ($encounter->chief_complaint)
                                        <div><dt class="inline font-semibold text-[#14201E]">Chief complaint:</dt> <dd class="inline">{{ $encounter->chief_complaint }}</dd></div>
                                    @endif
                                    @if ($encounter->assessment)
                                        <div><dt class="inline font-semibold text-[#14201E]">Assessment:</dt> <dd class="inline">{{ $encounter->assessment }}</dd></div>
                                    @endif
                                    @if ($encounter->treatment_plan)
                                        <div><dt class="inline font-semibold text-[#14201E]">Treatment plan:</dt> <dd class="inline">{{ $encounter->treatment_plan }}</dd></div>
                                    @endif
                                    @if ($encounter->clinical_notes)
                                        <div><dt class="inline font-semibold text-[#14201E]">Notes:</dt> <dd class="inline">{{ $encounter->clinical_notes }}</dd></div>
                                    @endif
                                </dl>
                            @endif

                            @isset ($encounter->vitals)
                                <div>
                                    <p class="section-title mb-2">Vital signs</p>
                                    <dl class="grid grid-cols-2 divide-x divide-y divide-[#DCE3E1] border border-[#DCE3E1] bg-[#F4F7F6]/60 sm:grid-cols-4">
                                        <div class="px-3 py-2">
                                            <dt class="text-[11px] font-medium text-[#465854] uppercase">Temp</dt>
                                            <dd class="cs-num text-sm font-medium text-[#14201E]">{{ $encounter->vitals->temperature_c ?? '—' }} °C</dd>
                                        </div>
                                        <div class="px-3 py-2">
                                            <dt class="text-[11px] font-medium text-[#465854] uppercase">BP</dt>
                                            <dd class="cs-num text-sm font-medium text-[#14201E]">
                                                @if ($encounter->vitals->systolic_bp && $encounter->vitals->diastolic_bp)
                                                    {{ $encounter->vitals->systolic_bp }}/{{ $encounter->vitals->diastolic_bp }} mmHg
                                                @else
                                                    —
                                                @endif
                                            </dd>
                                        </div>
                                        <div class="px-3 py-2">
                                            <dt class="text-[11px] font-medium text-[#465854] uppercase">Heart rate</dt>
                                            <dd class="cs-num text-sm font-medium text-[#14201E]">{{ $encounter->vitals->heart_rate ?? '—' }} bpm</dd>
                                        </div>
                                        <div class="px-3 py-2">
                                            <dt class="text-[11px] font-medium text-[#465854] uppercase">Resp rate</dt>
                                            <dd class="cs-num text-sm font-medium text-[#14201E]">{{ $encounter->vitals->respiratory_rate ?? '—' }} /min</dd>
                                        </div>
                                        <div class="px-3 py-2">
                                            <dt class="text-[11px] font-medium text-[#465854] uppercase">Weight</dt>
                                            <dd class="cs-num text-sm font-medium text-[#14201E]">{{ $encounter->vitals->weight_kg ?? '—' }} kg</dd>
                                        </div>
                                        <div class="px-3 py-2">
                                            <dt class="text-[11px] font-medium text-[#465854] uppercase">Height</dt>
                                            <dd class="cs-num text-sm font-medium text-[#14201E]">{{ $encounter->vitals->height_cm ?? '—' }} cm</dd>
                                        </div>
                                        <div class="px-3 py-2">
                                            <dt class="text-[11px] font-medium text-[#465854] uppercase">SpO₂</dt>
                                            <dd class="cs-num text-sm font-medium text-[#14201E]">{{ $encounter->vitals->spo2 ?? '—' }} %</dd>
                                        </div>
                                        <div class="px-3 py-2">
                                            <dt class="text-[11px] font-medium text-[#465854] uppercase">BMI</dt>
                                            <dd class="cs-num text-sm font-medium text-[#14201E]">{{ $encounter->vitals->bmi ?? '—' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            @endisset

                            @if ($encounter->prescriptions?->isNotEmpty())
                                <div>
                                    <p class="section-title mb-2">Prescriptions issued</p>
                                    <ul class="divide-y divide-[#DCE3E1] border-y border-[#DCE3E1]">
                                        @foreach ($encounter->prescriptions as $prescription)
                                            <li class="py-2 text-sm">
                                                <span class="font-medium text-[#14201E]">{{ $prescription->medication_name }}</span>
                                                @if ($prescription->strength)
                                                    <span class="text-[#465854]">{{ $prescription->strength }}</span>
                                                @endif
                                                <span class="cs-num text-[#465854]"> — {{ $prescription->dosage }}, {{ $prescription->frequency }}, {{ $prescription->duration }}</span>
                                                @if ($prescription->instructions)
                                                    <p class="mt-0.5 text-xs text-[#465854]">{{ $prescription->instructions }}</p>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="p-6">
                    <x-empty icon="clipboard" title="No consultations recorded yet."
                             description="Notes, vitals and prescriptions from your clinic visits appear here." />
                </div>
            @endif
        </div>
        @if ($encounters->hasPages())
            <div class="border-t border-[#DCE3E1] px-5 py-3.5">{{ $encounters->links() }}</div>
        @endif
    </section>

    {{-- E-prescriptions --}}
    <section id="prescriptions" class="card mt-6 scroll-mt-20">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>E-prescriptions</x-card-title>
            <span class="cs-num text-xs text-[#465854]">{{ $prescriptions->total() }} issued</span>
        </div>
        <div class="card-body p-0">
            <div class="divide-y divide-[#DCE3E1] md:hidden">
                @forelse ($prescriptions as $prescription)
                    <article class="space-y-2 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="cs-num font-mono text-xs font-semibold text-[#0F766E]">{{ $prescription->prescription_number }}</span>
                            <span class="cs-num text-xs text-[#465854]">{{ $prescription->issued_at?->isoFormat('MMM D, YYYY') ?? '—' }}</span>
                        </div>
                        <p class="text-sm font-medium text-[#14201E]">
                            {{ $prescription->medication_name }}
                            @if ($prescription->strength)
                                <span class="text-[#465854]">{{ $prescription->strength }}</span>
                            @endif
                            @if ($prescription->dosage_form)
                                <span class="text-xs text-[#465854]">({{ \Illuminate\Support\Str::headline($prescription->dosage_form) }})</span>
                            @endif
                        </p>
                        <p class="cs-num text-xs text-[#465854]">
                            {{ $prescription->dosage }}, {{ $prescription->frequency }}, {{ $prescription->route }}, {{ $prescription->duration }}
                            @if ($prescription->quantity)
                                · Qty: {{ $prescription->quantity }}
                            @endif
                        </p>
                        @if ($prescription->instructions)
                            <p class="text-xs text-[#465854]">{{ $prescription->instructions }}</p>
                        @endif
                        <p class="pt-1 text-xs text-[#465854]">
                            Dr. {{ $prescription->physician?->user?->name ?? '—' }}
                        </p>
                    </article>
                @empty
                    <div class="p-6">
                        <x-empty icon="document" title="No prescriptions issued yet."
                                 description="Prescriptions sent to the pharmacy appear here." />
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full">
                    <caption class="sr-only">Patient e-prescriptions</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="table-head">Rx number</th>
                            <th scope="col" class="table-head">Medication</th>
                            <th scope="col" class="table-head">Directions</th>
                            <th scope="col" class="table-head">Issued</th>
                            <th scope="col" class="table-head">Physician</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#DCE3E1]">
                        @forelse ($prescriptions as $prescription)
                            <tr class="table-row">
                                <td class="table-cell cs-num font-mono font-semibold text-[#0F766E] whitespace-nowrap">{{ $prescription->prescription_number }}</td>
                                <td class="table-cell">
                                    <span class="font-medium text-[#14201E]">{{ $prescription->medication_name }}</span>
                                    @if ($prescription->strength)
                                        <span class="text-[#465854]">{{ $prescription->strength }}</span>
                                    @endif
                                    @if ($prescription->dosage_form)
                                        <span class="block text-xs text-[#465854]">{{ \Illuminate\Support\Str::headline($prescription->dosage_form) }}</span>
                                    @endif
                                </td>
                                <td class="table-cell cs-num text-[#14201E]">
                                    {{ $prescription->dosage }}, {{ $prescription->frequency }}, {{ $prescription->route }}, {{ $prescription->duration }}
                                    @if ($prescription->quantity)
                                        <span class="block text-xs text-[#465854]">Qty: {{ $prescription->quantity }}</span>
                                    @endif
                                    @if ($prescription->instructions)
                                        <span class="block text-xs text-[#465854]">{{ $prescription->instructions }}</span>
                                    @endif
                                </td>
                                <td class="table-cell cs-num whitespace-nowrap text-[#465854]">{{ $prescription->issued_at?->isoFormat('MMM D, YYYY') ?? '—' }}</td>
                                <td class="table-cell whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        @if ($prescription->physician?->user?->name)
                                            <x-avatar :name="$prescription->physician->user->name" size="sm" />
                                        @endif
                                        <span class="text-[#14201E]">Dr. {{ $prescription->physician?->user?->name ?? '—' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-10" colspan="5">
                                    <x-empty icon="document" title="No prescriptions issued yet."
                                             description="Prescriptions sent to the pharmacy appear here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($prescriptions->hasPages())
            <div class="border-t border-[#DCE3E1] px-5 py-3.5">{{ $prescriptions->links() }}</div>
        @endif
    </section>
@endsection
