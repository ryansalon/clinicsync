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
            {{-- Status timeline --}}
            @php
                $isClosed = in_array($appointment->status, ['cancelled', 'no_show'], true);

                $steps = [
                    ['label' => 'Booked', 'time' => $appointment->created_at->isoFormat('MMM D · h:mm A'), 'done' => true],
                    [
                        'label' => 'Confirmed',
                        'time' => in_array($appointment->status, ['confirmed', 'completed'], true)
                            ? 'Confirmed by the clinic'
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
                <div class="alert alert-warning">
                    <x-icon name="exclamation-triangle" class="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <p class="font-semibold">This appointment was {{ \Illuminate\Support\Str::headline($appointment->status) }}.</p>
                        <p class="mt-0.5">
                            @if ($appointment->status === 'no_show')
                                The visit was marked as a no-show. You can book a new appointment anytime.
                            @else
                                The slot has been released. You can book a new appointment anytime.
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <section class="card">
                    <div class="card-body">
                        <ol class="flex items-start">
                            @foreach ($steps as $index => $step)
                                @php($isCurrent = $currentStep === $index)
                                <li class="relative flex-1 text-center">
                                    @if (! $loop->first)
                                        <span class="absolute top-[15px] -left-1/2 z-0 h-0.5 w-full {{ $step['done'] ? 'bg-teal-500' : 'bg-slate-200' }}"></span>
                                    @endif

                                    <span class="relative z-10 mx-auto flex h-8 w-8 items-center justify-center rounded-full ring-4 ring-white
                                        @if ($step['done'])
                                            bg-teal-600 text-white shadow-sm
                                        @elseif ($isCurrent)
                                            border-2 border-teal-500 bg-white
                                        @else
                                            border border-slate-200 bg-slate-100 text-slate-500
                                        @endif">
                                        @if ($step['done'])
                                            <x-icon name="check" class="h-4 w-4" />
                                        @elseif ($isCurrent)
                                            <span class="h-2 w-2 rounded-full bg-teal-500"></span>
                                        @else
                                            <span class="text-[11px] font-semibold">{{ $index + 1 }}</span>
                                        @endif
                                    </span>

                                    <p class="mt-2 text-xs font-semibold {{ $step['done'] || $isCurrent ? 'text-slate-900' : 'text-slate-500' }}">
                                        {{ $step['label'] }}
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">
                                        {{ $step['time'] ?? ($isCurrent ? 'In progress…' : 'Pending') }}
                                    </p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>
            @endif

            {{-- Details --}}
            <section class="card">
                <div class="card-header">
                    <x-card-title>Visit details</x-card-title>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Physician</dt>
                            <dd class="mt-1 flex items-center gap-3 text-sm text-slate-900">
                                @if ($appointment->physician?->user?->name)
                                    <x-avatar :name="$appointment->physician->user->name" size="sm" />
                                @endif
                                <span>Dr. {{ $appointment->physician?->user?->name ?? 'To be assigned' }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Specialization</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $appointment->physician?->specialization ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Date</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $appointment->appointment_date->isoFormat('dddd, MMM D, YYYY') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Time</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ date('g:i A', strtotime($appointment->start_time)) }}
                                @if ($appointment->end_time)
                                    – {{ date('g:i A', strtotime($appointment->end_time)) }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Queue number</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">#{{ $appointment->queue_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Booked on</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $appointment->created_at->isoFormat('MMMM D, YYYY · h:mm A') }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Chief complaint</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $appointment->chief_complaint ?: '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Reason</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $appointment->reason ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            {{-- Queue information --}}
            <section class="rounded-xl border border-teal-200 bg-teal-50 p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700">
                        <x-icon name="clock" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-teal-900">Queue information</h3>
                        <p class="mt-1 text-sm text-teal-800">
                            Please arrive at the clinic <span class="font-semibold">15 minutes early</span> and wait for your queue number
                            <span class="font-semibold">#{{ $appointment->queue_number }}</span> to be called. The queue display at the
                            reception shows whose turn it is, and you will also be notified here once your status changes.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Consultation summary --}}
            @if ($appointment->encounter)
                <section class="card">
                    <div class="card-header">
                        <x-card-title>Consultation summary</x-card-title>
                    </div>
                    <div class="card-body">
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Diagnosis</dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $appointment->encounter->diagnosis ?: '—' }}
                                    @if ($appointment->encounter->diagnosis_code)
                                        <span class="text-xs text-slate-500">(Code {{ $appointment->encounter->diagnosis_code }})</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Assessment</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $appointment->encounter->assessment ?: '—' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Treatment plan</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $appointment->encounter->treatment_plan ?: '—' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">Clinical notes</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $appointment->encounter->clinical_notes ?: '—' }}</dd>
                            </div>
                        </dl>

                        @if ($appointment->encounter->prescriptions?->isNotEmpty())
                            <div class="mt-5">
                                <p class="section-title mb-2">Prescriptions</p>
                                <ul class="divide-y divide-slate-100 rounded-lg border border-slate-200">
                                    @foreach ($appointment->encounter->prescriptions as $prescription)
                                        <li class="px-3 py-2 text-sm">
                                            <span class="font-medium text-slate-900">{{ $prescription->medication_name }}</span>
                                            @if ($prescription->strength)
                                                <span class="text-slate-600">{{ $prescription->strength }}</span>
                                            @endif
                                            <span class="text-slate-500">
                                                — {{ $prescription->dosage }}, {{ $prescription->frequency }}, {{ $prescription->duration }}
                                            </span>
                                            @if ($prescription->instructions)
                                                <p class="mt-0.5 text-xs text-slate-500">{{ $prescription->instructions }}</p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            {{-- Actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5">
                <a class="btn btn-secondary" href="{{ route('patient.appointments.index') }}">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Back to my appointments
                </a>

                @if ($appointment->isCancellable())
                    <div class="flex flex-wrap gap-3">
                        <a class="btn btn-secondary" href="{{ route('patient.appointments.reschedule', $appointment) }}">
                            <x-icon name="refresh" class="h-4 w-4" />
                            Reschedule
                        </a>
                        <form method="POST" action="{{ route('patient.appointments.destroy', $appointment) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"
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
