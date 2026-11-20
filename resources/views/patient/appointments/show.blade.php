@extends('layouts.app')

@section('title', 'Appointment details')

@section('content')
    <div class="mx-auto max-w-4xl">
        <x-breadcrumb :items="[
            ['label' => 'My appointments', 'href' => route('patient.appointments.index')],
            ['label' => 'Appointment #'.($appointment->queue_number ?? $appointment->id)],
        ]" />

        <x-page-header title="Appointment details"
                       description="Reference {{ $appointment->queue_number ? '#'.$appointment->queue_number : $appointment->id }}">
            <x-slot:actions>
                <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
            </x-slot:actions>
        </x-page-header>

        <div class="space-y-6">
            @php
                $isClosed = in_array($appointment->status, ['cancelled', 'no_show'], true);
                $isActiveUpcoming = in_array($appointment->status, ['pending', 'confirmed'], true);

                $steps = [
                    ['label' => 'Booked', 'time' => $appointment->created_at->isoFormat('MMM D · h:mm A'), 'done' => true],
                    [
                        'label' => 'Confirmed',
                        'time' => in_array($appointment->status, ['confirmed', 'completed'], true)
                            ? 'Confirmed by clinic'
                            : null,
                        'done' => in_array($appointment->status, ['confirmed', 'completed'], true),
                    ],
                    [
                        'label' => 'Called',
                        'time' => $appointment->called_at?->isoFormat('MMM D · h:mm A'),
                        'done' => $appointment->called_at !== null,
                    ],
                    [
                        'label' => 'Completed',
                        'time' => $appointment->finished_at?->isoFormat('MMM D · h:mm A'),
                        'done' => $appointment->status === \App\Models\Appointment::STATUS_COMPLETED,
                    ],
                ];

                $currentStep = collect($steps)->search(fn (array $step) => ! $step['done']);
                $currentStep = $currentStep === false ? null : $currentStep;
            @endphp

            @if ($isClosed)
                <x-alert type="warning" title="Appointment {{ \Illuminate\Support\Str::headline($appointment->status) }}">
                    @if ($appointment->status === 'no_show')
                        This visit was marked as a no-show. You may book another appointment anytime.
                    @else
                        This slot has been released. You may book another appointment anytime.
                    @endif
                </x-alert>
            @else
                <section class="card">
                    <div class="card-body">
                        <ol class="flex items-start" aria-label="Appointment timeline">
                            @foreach ($steps as $index => $step)
                                @php
                                    $isCurrent = $currentStep === $index;
                                @endphp
                                <li class="relative flex-1 text-center">
                                    @if (! $loop->first)
                                        <span class="absolute top-[15px] -left-1/2 z-0 h-0.5 w-full {{ $step['done'] ? 'bg-brand' : 'bg-hairline' }}"></span>
                                    @endif

                                    <span class="relative z-10 mx-auto flex h-8 w-8 items-center justify-center rounded-full ring-4 ring-surface
                                        @if ($step['done'])
                                            bg-brand text-white
                                        @elseif ($isCurrent)
                                            border-2 border-brand bg-brand-tint text-brand
                                        @else
                                            border border-hairline bg-surface-subtle text-meta
                                        @endif">
                                        @if ($step['done'])
                                            <x-icon name="check" class="h-4 w-4" />
                                        @elseif ($isCurrent)
                                            <span class="h-2 w-2 rounded-full bg-brand"></span>
                                        @else
                                            <span class="cs-num font-mono text-[11px] font-semibold">{{ $index + 1 }}</span>
                                        @endif
                                    </span>

                                    <p class="mt-2 text-xs font-semibold {{ $step['done'] || $isCurrent ? 'text-bright' : 'text-meta' }}">
                                        {{ $step['label'] }}
                                    </p>
                                    <p class="cs-num mt-0.5 text-[11px] text-meta">
                                        {{ $step['time'] ?? ($isCurrent ? 'In progress' : 'Pending') }}
                                    </p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>
            @endif

            {{-- Visit details --}}
            <section class="card overflow-hidden">
                <div class="card-header">
                    <x-card-title>Visit details</x-card-title>
                </div>
                <div class="card-body p-0">
                    <dl class="grid grid-cols-1 divide-y divide-hairline sm:grid-cols-2 sm:divide-x">
                        <div class="px-5 py-3.5">
                            <dt class="section-title">Physician</dt>
                            <dd class="mt-1.5 flex items-center gap-2.5 text-sm font-semibold text-bright">
                                @if ($appointment->physician?->user?->name)
                                    <x-avatar :name="$appointment->physician->user->name" size="sm" />
                                @endif
                                <span>Dr. {{ $appointment->physician?->user?->name ?? 'To be assigned' }}</span>
                            </dd>
                        </div>
                        <div class="px-5 py-3.5">
                            <dt class="section-title">Specialization</dt>
                            <dd class="mt-1.5 text-sm text-bright">{{ $appointment->physician?->specialization ?? '—' }}</dd>
                        </div>
                        <div class="px-5 py-3.5">
                            <dt class="section-title">Date</dt>
                            <dd class="cs-num mt-1.5 text-sm font-medium text-bright">{{ $appointment->appointment_date->isoFormat('dddd, MMM D, YYYY') }}</dd>
                        </div>
                        <div class="px-5 py-3.5">
                            <dt class="section-title">Time</dt>
                            <dd class="cs-num font-mono mt-1.5 text-sm font-medium text-bright">
                                {{ date('g:i A', strtotime($appointment->start_time)) }}
                                @if ($appointment->end_time)
                                    – {{ date('g:i A', strtotime($appointment->end_time)) }}
                                @endif
                            </dd>
                        </div>
                        <div class="px-5 py-3.5">
                            <dt class="section-title">Queue number</dt>
                            <dd class="cs-num mt-1.5 font-mono text-sm font-semibold text-brand">#{{ $appointment->queue_number }}</dd>
                        </div>
                        <div class="px-5 py-3.5">
                            <dt class="section-title">Booked on</dt>
                            <dd class="cs-num mt-1.5 text-sm text-meta">{{ $appointment->created_at->isoFormat('MMM D, YYYY · h:mm A') }}</dd>
                        </div>
                        <div class="px-5 py-3.5 sm:col-span-2">
                            <dt class="section-title">Chief complaint</dt>
                            <dd class="mt-1.5 text-sm text-bright">{{ $appointment->chief_complaint ?: '—' }}</dd>
                        </div>
                        @if ($appointment->reason)
                            <div class="px-5 py-3.5 sm:col-span-2">
                                <dt class="section-title">Additional notes</dt>
                                <dd class="mt-1.5 text-sm text-bright">{{ $appointment->reason }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </section>

            {{-- Arrival guidance only for upcoming/active appointments --}}
            @if ($isActiveUpcoming)
                <x-alert type="info" title="Queue & check-in instructions">
                    Arrive <strong class="font-semibold">15 minutes before your slot</strong> and watch for queue number
                    <strong class="cs-num font-mono font-semibold">#{{ $appointment->queue_number }}</strong>. Clinic hours: Mon–Sat, 08:00–17:00.
                </x-alert>
            @endif

            {{-- Consultation summary --}}
            @if ($appointment->encounter)
                <section class="card">
                    <div class="card-header">
                        <x-card-title>Consultation summary</x-card-title>
                    </div>
                    <div class="card-body space-y-5">
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="section-title">Diagnosis</dt>
                                <dd class="mt-1 font-medium text-bright">
                                    {{ $appointment->encounter->diagnosis ?: '—' }}
                                    @if ($appointment->encounter->diagnosis_code)
                                        <span class="cs-num font-mono text-xs text-meta">({{ $appointment->encounter->diagnosis_code }})</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($appointment->encounter->assessment)
                                <div>
                                    <dt class="section-title">Assessment</dt>
                                    <dd class="mt-1 text-bright">{{ $appointment->encounter->assessment }}</dd>
                                </div>
                            @endif
                            @if ($appointment->encounter->treatment_plan)
                                <div>
                                    <dt class="section-title">Treatment plan</dt>
                                    <dd class="mt-1 text-bright">{{ $appointment->encounter->treatment_plan }}</dd>
                                </div>
                            @endif
                            @if ($appointment->encounter->clinical_notes)
                                <div>
                                    <dt class="section-title">Clinical notes</dt>
                                    <dd class="mt-1 text-bright">{{ $appointment->encounter->clinical_notes }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($appointment->encounter->prescriptions?->isNotEmpty())
                            <div>
                                <p class="section-title mb-2">Prescriptions</p>
                                <ul class="divide-y divide-hairline border-y border-hairline">
                                    @foreach ($appointment->encounter->prescriptions as $prescription)
                                        <li class="py-2.5 text-sm">
                                            <span class="font-semibold text-bright">{{ $prescription->medication_name }}</span>
                                            @if ($prescription->strength)
                                                <span class="text-meta">{{ $prescription->strength }}</span>
                                            @endif
                                            <span class="cs-num text-meta">
                                                — {{ $prescription->dosage }}, {{ $prescription->frequency }}, {{ $prescription->duration }}
                                            </span>
                                            @if ($prescription->instructions)
                                                <p class="mt-0.5 text-xs text-meta">{{ $prescription->instructions }}</p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            {{-- Actions (destructive Cancel separated cleanly with outline-danger styling) --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-hairline pt-5">
                <a class="btn btn-secondary" href="{{ route('patient.appointments.index') }}">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Back to my appointments
                </a>

                @if ($appointment->isCancellable())
                    <div class="flex flex-wrap items-center gap-4">
                        <a class="btn btn-secondary" href="{{ route('patient.appointments.reschedule', $appointment) }}">
                            <x-icon name="refresh" class="h-4 w-4" />
                            Reschedule
                        </a>
                        <form method="POST" action="{{ route('patient.appointments.destroy', $appointment) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger"
                                    data-confirm="Cancel this appointment? The slot will be released for other patients.">
                                <x-icon name="x" class="h-4 w-4" />
                                Cancel appointment
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
