@extends('layouts.app')

@section('title', 'My appointments')

@section('content')
    <x-page-header title="My appointments" description="Review, track and manage your clinic visits.">
        <x-slot:actions>
            <a class="btn btn-primary" href="{{ route('patient.appointments.create') }}">
                <x-icon name="plus" class="h-4 w-4" />
                Book new appointment
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Status filter --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <span class="mr-1 text-slate-400">
            <x-icon name="funnel" class="h-4 w-4" />
        </span>
        <a href="{{ route('patient.appointments.index') }}"
           class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition {{ $currentStatus === null ? 'border-teal-600 bg-teal-50 text-teal-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900' }}">
            All
        </a>
        @foreach (['pending', 'confirmed', 'completed', 'cancelled', 'no_show'] as $status)
            <a href="{{ route('patient.appointments.index', ['status' => $status]) }}"
               class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-medium transition {{ $currentStatus === $status ? 'border-teal-600 bg-teal-50 font-semibold text-teal-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900' }}">
                {{ \Illuminate\Support\Str::headline($status) }}
            </a>
        @endforeach
    </div>

    @php
        $statusDots = [
            'pending' => 'bg-amber-500',
            'confirmed' => 'bg-sky-500',
            'completed' => 'bg-emerald-500',
            'cancelled' => 'bg-slate-400',
            'no_show' => 'bg-rose-500',
        ];
    @endphp

    @if ($appointments->isNotEmpty())
        <ol class="relative space-y-5 before:absolute before:inset-y-3 before:left-[17px] before:w-0.5 before:bg-slate-200 before:content-['']">
            @foreach ($appointments as $appointment)
                <li class="relative flex gap-4">
                    <span class="relative z-10 mt-6 flex h-9 w-9 shrink-0 items-center justify-center rounded-full shadow-sm ring-4 ring-white {{ $statusDots[$appointment->status] ?? 'bg-slate-400' }}">
                        <x-icon name="calendar" class="h-4 w-4 text-white" />
                    </span>

                    <article class="card min-w-0 flex-1 transition hover:shadow-md">
                        <div class="card-body py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ date('g:i A', strtotime($appointment->start_time)) }}
                                            @if ($appointment->end_time)
                                                <span class="font-normal text-slate-400">– {{ date('g:i A', strtotime($appointment->end_time)) }}</span>
                                            @endif
                                        </p>
                                        <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                                        <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                                    </div>

                                    <p class="mt-1 text-xs text-slate-500">{{ $appointment->appointment_date->isoFormat('dddd, MMM D, YYYY') }}</p>

                                    <div class="mt-2.5 flex flex-wrap items-center gap-3">
                                        <span class="flex items-center gap-2 text-sm text-slate-700">
                                            @if ($appointment->physician?->user?->name)
                                                <x-avatar :name="$appointment->physician->user->name" size="sm" />
                                                Dr. {{ $appointment->physician->user->name }}
                                            @else
                                                Physician to be assigned
                                            @endif
                                        </span>
                                        <span class="chip">
                                            <x-icon name="list" class="h-3.5 w-3.5 text-slate-400" />
                                            Queue #{{ $appointment->queue_number }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex shrink-0 flex-wrap gap-2">
                                    <a class="btn btn-secondary btn-sm" href="{{ route('patient.appointments.show', $appointment) }}">
                                        <x-icon name="eye" class="h-4 w-4" />
                                        View
                                    </a>
                                    @if ($appointment->isCancellable())
                                        <a class="btn btn-ghost btn-sm" href="{{ route('patient.appointments.reschedule', $appointment) }}">
                                            <x-icon name="refresh" class="h-4 w-4" />
                                            Reschedule
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                </li>
            @endforeach
        </ol>

        <div class="mt-6">
            {{ $appointments->links() }}
        </div>
    @else
        <x-empty icon="calendar"
                 title="{{ $currentStatus ? 'No '.\Illuminate\Support\Str::headline($currentStatus).' appointments found.' : 'You have no appointments yet.' }}"
                 description="Keep track of every clinic visit from this page.">
            <x-slot:action>
                <a class="btn btn-primary" href="{{ route('patient.appointments.create') }}">
                    <x-icon name="plus" class="h-4 w-4" />
                    Book one now
                </a>
            </x-slot:action>
        </x-empty>
    @endif
@endsection
