@extends('layouts.app')

@section('title', 'Patient dashboard')

@section('content')
    <x-page-header title="Patient dashboard" description="A quick overview of your upcoming visits and medical records.">
        @if ($patient)
            <p class="mt-1 text-xs text-slate-500">
                Record #{{ $patient->patient_number }}
                · {{ $patient->date_of_birth?->isoFormat('MMMM D, YYYY') ?? '—' }}
                · {{ $patient->sex ? \Illuminate\Support\Str::headline($patient->sex) : '—' }}
                · {{ $patient->blood_type ?: 'No blood type on file' }}
                · {{ $patient->address ?: 'No address on file' }}
            </p>
        @endif

        <x-slot:actions>
            @if ($patient)
                <div class="flex flex-wrap items-center gap-3">
                    <a class="btn btn-primary" href="{{ route('patient.appointments.create') }}">
                        <x-icon name="plus" class="h-4 w-4" />
                        Book an appointment
                    </a>
                    <a class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 transition hover:text-teal-700 px-2 py-1.5"
                       href="{{ route('patient.appointments.index') }}">
                        <x-icon name="calendar" class="h-3.5 w-3.5 text-slate-400" />
                        My appointments
                    </a>
                    <a class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 transition hover:text-teal-700 px-2 py-1.5"
                       href="{{ route('patient.records') }}">
                        <x-icon name="document" class="h-3.5 w-3.5 text-slate-400" />
                        Medical records
                    </a>
                </div>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if ($patient)
        {{-- Live queue status (today's appointment) with polling --}}
        <div id="live-queue-status" data-poll data-poll-url="{{ route('patient.dashboard') }}" data-poll-interval="30000">
            @include('patient.partials.queue-status')
        </div>

        {{-- Summary tiles --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-stat label="Appointments" value="{{ $appointmentCount }}" icon="calendar" tone="teal" />
            <x-stat label="Consultations" value="{{ $encounterCount }}" icon="clipboard-check" tone="sky" />
            <x-stat label="Prescriptions" value="{{ $prescriptionCount }}" icon="document" tone="indigo" />
            <x-stat label="Active allergies" value="{{ $activeAllergies->count() }}" icon="exclamation-triangle" tone="rose" />
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            {{-- Upcoming appointments --}}
            <section class="card lg:col-span-2">
                <div class="card-header flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold tracking-tight text-slate-900">Upcoming appointments</h3>
                    <span class="text-xs text-slate-500">{{ $upcoming->count() }} scheduled</span>
                </div>
                <div class="card-body p-0">
                    {{-- Mobile stacked cards (below md) --}}
                    <div class="divide-y divide-slate-100 md:hidden">
                        @forelse ($upcoming as $appointment)
                            <article class="p-4 space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-medium text-xs text-slate-900">
                                        {{ $appointment->appointment_date->isoFormat('ddd, MMM D, YYYY') }}
                                    </span>
                                    <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        @if ($appointment->physician?->user?->name)
                                            <x-avatar :name="$appointment->physician->user->name" size="sm" />
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-slate-900">Dr. {{ $appointment->physician?->user?->name ?? '—' }}</p>
                                            <p class="text-xs text-slate-500">
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
                                         description="Your booked visits will appear here with their queue numbers.">
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

                    {{-- Desktop table (md and up) --}}
                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="table-head">Date</th>
                                    <th class="table-head">Time</th>
                                    <th class="table-head">Physician</th>
                                    <th class="table-head">Queue</th>
                                    <th class="table-head">Status</th>
                                    <th class="table-head text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($upcoming as $appointment)
                                    <tr class="table-row">
                                        <td class="table-cell whitespace-nowrap">{{ $appointment->appointment_date->isoFormat('dddd, MMM D, YYYY') }}</td>
                                        <td class="table-cell whitespace-nowrap">
                                            {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }}
                                            @if ($appointment->end_time)
                                                <span class="text-slate-400">– {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}</span>
                                            @endif
                                        </td>
                                        <td class="table-cell">
                                            <div class="flex items-center gap-3">
                                                @if ($appointment->physician?->user?->name)
                                                    <x-avatar :name="$appointment->physician->user->name" size="sm" />
                                                @endif
                                                <span class="whitespace-nowrap">Dr. {{ $appointment->physician?->user?->name ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td class="table-cell whitespace-nowrap font-medium text-slate-900">#{{ $appointment->queue_number }}</td>
                                        <td class="table-cell">
                                            <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                                        </td>
                                        <td class="table-cell text-right">
                                            <a class="btn btn-secondary btn-sm" href="{{ route('patient.appointments.show', $appointment) }}">
                                                <x-icon name="eye" class="h-4 w-4" />
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-4 py-10" colspan="6">
                                            <x-empty icon="calendar" title="No upcoming appointments."
                                                     description="Your booked visits will appear here with their queue numbers.">
                                                <x-slot:action>
                                                    <a class="btn btn-primary" href="{{ route('patient.appointments.create') }}">
                                                        <x-icon name="plus" class="h-4 w-4" />
                                                        Book one now
                                                    </a>
                                                </x-slot:action>
                                            </x-empty>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- Latest consultation --}}
            <section class="card">
                <div class="card-header">
                    <h3 class="text-sm font-semibold tracking-tight text-slate-900">Latest consultation</h3>
                </div>
                <div class="card-body">
                    @if ($latestEncounter)
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $latestEncounter->diagnosis ?: 'Diagnosis not yet recorded' }}
                        </p>
                        @if ($latestEncounter->diagnosis_code)
                            <p class="mt-0.5 text-xs text-slate-500">Code {{ $latestEncounter->diagnosis_code }}</p>
                        @endif
                        <p class="mt-3 text-sm text-slate-600">{{ $latestEncounter->encountered_at->isoFormat('dddd, MMM D, YYYY') }}</p>
                        <div class="mt-1 flex items-center gap-2.5">
                            @if ($latestEncounter->physician?->user?->name)
                                <x-avatar :name="$latestEncounter->physician->user->name" size="sm" />
                            @endif
                            <p class="text-sm text-slate-600">Dr. {{ $latestEncounter->physician?->user?->name ?? '—' }}</p>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">Encounter {{ $latestEncounter->encounter_number }}</p>

                        <div class="mt-4 flex items-center justify-between gap-3">
                            <span class="badge badge-{{ $latestEncounter->status }}">
                                {{ \Illuminate\Support\Str::headline($latestEncounter->status) }}
                            </span>
                            <a href="{{ route('patient.records') }}" class="inline-flex items-center gap-1 text-sm font-medium text-teal-600 transition hover:text-teal-700">
                                View records
                                <x-icon name="arrow-right" class="h-4 w-4" />
                            </a>
                        </div>
                    @else
                        <x-empty icon="clipboard" title="No consultations yet."
                                 description="Your consultation history will appear here after your first visit." />
                    @endif
                </div>
            </section>
        </div>

        {{-- Physician availability calendar --}}
        <section class="mt-6 card">
            <div class="card-header flex items-center justify-between gap-2">
                <h3 class="text-sm font-semibold tracking-tight text-slate-900">Physician availability</h3>
                <a class="text-xs font-semibold text-teal-600 hover:text-teal-700" href="{{ route('patient.appointments.create') }}">Book a visit</a>
            </div>
            <div class="card-body">
                <x-availability-calendar
                    mode="link"
                    :url="route('patient.availability.calendar')"
                    :book-url="route('patient.appointments.create')" />
            </div>
        </section>

        {{-- Active allergies --}}
        @if ($activeAllergies->isNotEmpty())
            <section class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                        <x-icon name="exclamation-triangle" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-semibold text-rose-900">Active allergies</h3>
                        <p class="mt-0.5 text-xs text-rose-700">Please inform the physician and clinic staff of these allergies during your visit.</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($activeAllergies as $allergy)
                                <li class="rounded-lg border border-rose-200 bg-white px-3 py-2">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-sm font-medium text-slate-900">{{ $allergy->allergen }}</span>
                                        <span class="badge badge-{{ $allergy->severity }}">
                                            {{ \Illuminate\Support\Str::headline($allergy->severity) }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-slate-600">{{ $allergy->reaction }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
        @endif
    @else
        <x-empty icon="user" title="Patient profile not linked"
                 description="Your account is not yet linked to a patient record. Please visit the clinic reception to complete your registration." />
    @endif
@endsection
