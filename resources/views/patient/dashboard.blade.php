@extends('layouts.app')

@section('title', 'Patient dashboard')

@section('content')
    <x-page-header title="Patient dashboard" description="Upcoming clinic visits, live queue progress, and medical summary · Mon–Sat, 08:00–17:00.">
        @if ($patient)
            <div class="mt-2.5 inline-flex max-w-full flex-wrap items-center gap-x-4 gap-y-1 rounded-md border border-hairline bg-surface px-3 py-1.5 text-xs">
                <span class="cs-num font-mono font-semibold text-brand">
                    Record #{{ $patient->patient_number }}
                </span>
                <span class="cs-num text-read">
                    <span class="text-meta">DOB:</span> {{ $patient->date_of_birth?->isoFormat('MMM D, YYYY') ?? '—' }}
                </span>
                <span class="text-read">
                    <span class="text-meta">Sex:</span> {{ $patient->sex ? \Illuminate\Support\Str::headline($patient->sex) : '—' }}
                </span>
                <span class="text-read">
                    <span class="text-meta">Blood:</span> <span class="font-mono font-medium">{{ $patient->blood_type ?: 'Not on file' }}</span>
                </span>
            </div>
        @endif

        <x-slot:actions>
            @if ($patient)
                <a class="btn btn-primary" href="{{ route('patient.appointments.create') }}">
                    <x-icon name="plus" class="h-4 w-4" />
                    Book an appointment
                </a>
                <a class="btn btn-secondary" href="{{ route('patient.appointments.index') }}">
                    <x-icon name="calendar" class="h-4 w-4" />
                    My appointments
                </a>
                <a class="btn btn-ghost" href="{{ route('patient.records') }}">
                    <x-icon name="document" class="h-4 w-4" />
                    Medical records
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if ($patient)
        {{-- Live queue pass (today's appointment) with polling --}}
        <div id="live-queue-status" aria-live="polite" data-poll data-poll-url="{{ route('patient.dashboard') }}" data-poll-interval="30000">
            @include('patient.partials.queue-status')
        </div>

        @if ($needsProfileCompletion)
            <x-alert type="warning" class="mb-5" title="Profile details incomplete">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span>Add your blood type and emergency contact details for safer outpatient triage.</span>
                    <a href="{{ route('profile.edit') }}" class="btn btn-secondary btn-sm">Update profile</a>
                </div>
            </x-alert>
        @endif

        @if ($activeAllergies->isNotEmpty())
            <x-alert type="error" class="mb-5" title="Active allergies on file ({{ $activeAllergies->count() }})">
                <p class="mb-2 text-xs">Confirm these sensitivities with clinic staff and your attending physician during check-in.</p>
                <ul class="divide-y divide-danger-border border-t border-danger-border">
                    @foreach ($activeAllergies as $allergy)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
                            <span>
                                <strong class="font-semibold">{{ $allergy->allergen }}</strong>
                                <span class="text-xs opacity-90"> — {{ $allergy->reaction ?: 'Reaction not specified' }}</span>
                            </span>
                            <span class="badge badge-{{ $allergy->severity }}">
                                {{ \Illuminate\Support\Str::headline($allergy->severity) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        {{-- Summary ledger --}}
        <div class="ledger-strip sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('patient.appointments.index') }}" class="ledger-cell group hover:bg-canvas">
                <div class="flex items-start justify-between gap-2">
                    <span class="stat-label">Appointments</span>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-brand-border bg-brand-tint text-brand">
                        <x-icon name="calendar" class="h-4 w-4" />
                    </span>
                </div>
                <div class="flex items-end justify-between gap-2">
                    <div>
                        <p class="cs-num text-2xl font-bold text-bright">{{ $appointmentCount }}</p>
                        <p class="mt-0.5 text-xs text-meta">Upcoming &amp; past visits</p>
                    </div>
                    <span class="text-xs font-semibold text-brand opacity-0 transition group-hover:opacity-100">View &rarr;</span>
                </div>
            </a>
            <a href="{{ route('patient.records') }}" class="ledger-cell group hover:bg-canvas">
                <div class="flex items-start justify-between gap-2">
                    <span class="stat-label">Consultations</span>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-hairline bg-surface-subtle text-meta">
                        <x-icon name="clipboard" class="h-4 w-4" />
                    </span>
                </div>
                <div class="flex items-end justify-between gap-2">
                    <div>
                        <p class="cs-num text-2xl font-bold text-bright">{{ $encounterCount }}</p>
                        <p class="mt-0.5 text-xs text-meta">Physician summaries</p>
                    </div>
                    <span class="text-xs font-semibold text-brand opacity-0 transition group-hover:opacity-100">Records &rarr;</span>
                </div>
            </a>
            <a href="{{ route('patient.records') }}#prescriptions" class="ledger-cell group hover:bg-canvas">
                <div class="flex items-start justify-between gap-2">
                    <span class="stat-label">Prescriptions</span>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-hairline bg-surface-subtle text-meta">
                        <x-icon name="document" class="h-4 w-4" />
                    </span>
                </div>
                <div class="flex items-end justify-between gap-2">
                    <div>
                        <p class="cs-num text-2xl font-bold text-bright">{{ $prescriptionCount }}</p>
                        <p class="mt-0.5 text-xs text-meta">Issued medications</p>
                    </div>
                    <span class="text-xs font-semibold text-brand opacity-0 transition group-hover:opacity-100">History &rarr;</span>
                </div>
            </a>
            <div class="ledger-cell">
                <div class="flex items-start justify-between gap-2">
                    <span class="stat-label">Active allergies</span>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border {{ $activeAllergies->isNotEmpty() ? 'border-danger-border bg-danger-tint text-danger' : 'border-hairline bg-surface-subtle text-meta' }}">
                        <x-icon name="exclamation-triangle" class="h-4 w-4" />
                    </span>
                </div>
                <div>
                    <p class="cs-num text-2xl font-bold {{ $activeAllergies->isNotEmpty() ? 'text-danger' : 'text-bright' }}">{{ $activeAllergies->count() }}</p>
                    <p class="mt-0.5 text-xs text-meta">Recorded sensitivities</p>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            {{-- Upcoming appointments --}}
            <section class="card lg:col-span-2">
                <div class="card-header flex flex-wrap items-center justify-between gap-2">
                    <x-card-title>Upcoming appointments</x-card-title>
                    <span class="cs-num font-mono text-xs text-meta">{{ $upcoming->count() }} scheduled</span>
                </div>
                <div class="card-body p-0">
                    {{-- Mobile stacked cards --}}
                    <div class="divide-y divide-hairline md:hidden">
                        @forelse ($upcoming as $appointment)
                            <article class="space-y-2.5 p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="cs-num font-mono text-xs font-semibold text-bright">
                                        {{ $appointment->appointment_date->isoFormat('ddd, MMM D, YYYY') }}
                                    </span>
                                    <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        @if ($appointment->physician?->user?->name)
                                            <x-avatar :name="$appointment->physician->user->name" size="sm" />
                                        @endif
                                        <div>
                                            <p class="text-sm font-semibold text-bright">Dr. {{ $appointment->physician?->user?->name ?? '—' }}</p>
                                            <p class="cs-num font-mono text-xs text-meta">
                                                {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }}
                                                @if ($appointment->end_time)
                                                    – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                                                @endif
                                                · Queue #{{ $appointment->queue_number }}
                                            </p>
                                        </div>
                                    </div>
                                    <a class="btn btn-secondary btn-sm" href="{{ route('patient.appointments.show', $appointment) }}">
                                        <x-icon name="eye" class="h-4 w-4" />
                                        View
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="p-6">
                                <x-empty icon="calendar" title="No upcoming appointments."
                                         description="Booked clinic visits and queue numbers appear here.">
                                    <x-slot:action>
                                        <a class="btn btn-primary" href="{{ route('patient.appointments.create') }}">
                                            <x-icon name="plus" class="h-4 w-4" />
                                            Book one now
                                        </a>
                                    </x-slot:action>
                                </x-empty>
                            </div>
                        @endforelse
                    </div>

                    {{-- Desktop table --}}
                    <div class="hidden overflow-x-auto md:block">
                        @if ($upcoming->isNotEmpty())
                            <table class="min-w-full">
                                <caption class="sr-only">Upcoming patient appointments</caption>
                                <thead>
                                    <tr>
                                        <th scope="col" class="table-head">Date</th>
                                        <th scope="col" class="table-head">Time</th>
                                        <th scope="col" class="table-head">Physician</th>
                                        <th scope="col" class="table-head">Queue</th>
                                        <th scope="col" class="table-head">Status</th>
                                        <th scope="col" class="table-head text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-hairline">
                                    @foreach ($upcoming as $appointment)
                                        <tr class="table-row">
                                            <td class="table-cell cs-num whitespace-nowrap text-bright">{{ $appointment->appointment_date->isoFormat('ddd, MMM D, YYYY') }}</td>
                                            <td class="table-cell cs-num font-mono text-xs whitespace-nowrap text-bright">
                                                {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }}
                                                @if ($appointment->end_time)
                                                    <span class="text-meta">– {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}</span>
                                                @endif
                                            </td>
                                            <td class="table-cell">
                                                <div class="flex items-center gap-2.5">
                                                    @if ($appointment->physician?->user?->name)
                                                        <x-avatar :name="$appointment->physician->user->name" size="sm" />
                                                    @endif
                                                    <span class="whitespace-nowrap font-medium text-bright">Dr. {{ $appointment->physician?->user?->name ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td class="table-cell cs-num font-mono font-semibold text-brand whitespace-nowrap">#{{ $appointment->queue_number }}</td>
                                            <td class="table-cell whitespace-nowrap">
                                                <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                                            </td>
                                            <td class="table-cell whitespace-nowrap text-right">
                                                <div class="inline-flex items-center gap-1.5">
                                                    @if ($appointment->isCancellable())
                                                        <a class="btn btn-secondary btn-sm" href="{{ route('patient.appointments.reschedule', $appointment) }}">
                                                            Reschedule
                                                        </a>
                                                    @endif
                                                    <a class="btn btn-secondary btn-sm" href="{{ route('patient.appointments.show', $appointment) }}">
                                                        <x-icon name="eye" class="h-4 w-4" />
                                                        View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-8">
                                <x-empty icon="calendar" title="No upcoming appointments."
                                         description="Booked clinic visits and queue numbers appear here.">
                                    <x-slot:action>
                                        <a class="btn btn-primary" href="{{ route('patient.appointments.create') }}">
                                            <x-icon name="plus" class="h-4 w-4" />
                                            Book one now
                                        </a>
                                    </x-slot:action>
                                </x-empty>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Latest consultation + recent prescriptions --}}
            <div class="space-y-6">
                <section class="card">
                    <div class="card-header">
                        <x-card-title>Latest consultation</x-card-title>
                    </div>
                    <div class="card-body">
                        @if ($latestEncounter)
                            <p class="text-sm font-semibold text-bright">
                                {{ $latestEncounter->diagnosis ?: 'Diagnosis not yet recorded' }}
                            </p>
                            @if ($latestEncounter->diagnosis_code)
                                <p class="cs-num mt-0.5 font-mono text-xs text-meta">Code {{ $latestEncounter->diagnosis_code }}</p>
                            @endif
                            <p class="cs-num mt-3 text-sm text-meta">{{ $latestEncounter->encountered_at->isoFormat('dddd, MMM D, YYYY') }}</p>
                            <div class="mt-1.5 flex items-center gap-2.5">
                                @if ($latestEncounter->physician?->user?->name)
                                    <x-avatar :name="$latestEncounter->physician->user->name" size="sm" />
                                @endif
                                <p class="text-sm font-medium text-bright">Dr. {{ $latestEncounter->physician?->user?->name ?? '—' }}</p>
                            </div>
                            <p class="cs-num mt-1 font-mono text-xs text-meta">{{ $latestEncounter->encounter_number }}</p>

                            <div class="mt-4 flex items-center justify-between gap-3 border-t border-hairline pt-3">
                                <span class="badge badge-{{ $latestEncounter->status }}">
                                    {{ \Illuminate\Support\Str::headline($latestEncounter->status) }}
                                </span>
                                <a href="{{ route('patient.records') }}" class="inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-brand hover:underline">
                                    View records
                                    <x-icon name="arrow-right" class="h-4 w-4" />
                                </a>
                            </div>
                        @else
                            <x-empty icon="clipboard" title="No consultations yet."
                                     description="Your consultation history appears here after your first visit." />
                        @endif
                    </div>
                </section>

                <section class="card">
                    <div class="card-header flex flex-wrap items-center justify-between gap-2">
                        <x-card-title>Recent prescriptions</x-card-title>
                        <a href="{{ route('patient.records') }}#prescriptions" class="text-xs font-semibold text-brand hover:underline">All records</a>
                    </div>
                    <div class="card-body p-0">
                        @if ($recentPrescriptions->isNotEmpty())
                            <ul class="divide-y divide-hairline">
                                @foreach ($recentPrescriptions as $rx)
                                    <li class="px-5 py-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-bright">{{ $rx->medication_name }}</p>
                                            <span class="cs-num font-mono text-xs text-meta">{{ $rx->strength }}</span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-meta">{{ $rx->instructions ?: 'Follow physician directions.' }}</p>
                                        <p class="cs-num mt-1 text-xs text-meta">
                                            {{ $rx->issued_at?->isoFormat('MMM D, YYYY') ?? '—' }}
                                            · Dr. {{ $rx->physician?->user?->name ?? '—' }}
                                        </p>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="px-5 py-2">
                                <x-empty icon="document" title="No prescriptions issued yet."
                                         description="Medications prescribed during consultations appear here." />
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>

        {{-- Collapsible Physician availability calendar keeps dashboard concise while remaining 1 click away --}}
        <details class="card mt-6 group">
            <summary class="card-header flex cursor-pointer list-none flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-bright">Check physician availability calendar</h2>
                    <p class="mt-0.5 text-xs text-meta">Preview open clinic dates and book directly with an attending physician.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand">
                    <span class="group-open:hidden">Show calendar</span>
                    <span class="hidden group-open:inline">Hide calendar</span>
                    <x-icon name="chevron-right" class="h-4 w-4 transition-transform group-open:rotate-90" />
                </span>
            </summary>
            <div class="card-body">
                <x-availability-calendar
                    mode="link"
                    :url="route('patient.availability.calendar')"
                    :book-url="route('patient.appointments.create')" />
            </div>
        </details>
    @else
        <x-empty icon="user" title="Patient profile not linked"
                 description="Your account is not yet linked to a patient record. Visit clinic reception to complete registration." />
    @endif
@endsection
