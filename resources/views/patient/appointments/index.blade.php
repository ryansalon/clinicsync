@extends('layouts.app')

@section('title', 'My appointments')

@section('content')
    <x-page-header title="My appointments" description="Track clinic visits, queue assignments, and schedule changes · Mon–Sat, 08:00–17:00.">
        <x-slot:actions>
            <a class="btn btn-primary" href="{{ route('patient.appointments.create') }}">
                <x-icon name="plus" class="h-4 w-4" />
                Book new appointment
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Status filter --}}
    <nav aria-label="Filter appointments by status" class="mb-5 flex flex-wrap items-center gap-2">
        <span class="mr-1 text-[#465854]" aria-hidden="true">
            <x-icon name="funnel" class="h-4 w-4" />
        </span>
        <a href="{{ route('patient.appointments.index') }}"
           class="inline-flex min-h-11 items-center gap-1.5 rounded-full border px-4 py-2 text-xs font-semibold transition {{ $currentStatus === null ? 'border-[#0F766E] bg-[#0F766E] text-white' : 'border-[#DCE3E1] bg-white text-[#465854] hover:border-[#0F766E]/40 hover:text-[#14201E]' }}">
            All
        </a>
        @foreach (['pending', 'confirmed', 'completed', 'cancelled', 'no_show'] as $status)
            <a href="{{ route('patient.appointments.index', ['status' => $status]) }}"
               class="inline-flex min-h-11 items-center gap-1.5 rounded-full border px-4 py-2 text-xs font-semibold transition {{ $currentStatus === $status ? 'border-[#0F766E] bg-[#0F766E] text-white' : 'border-[#DCE3E1] bg-white text-[#465854] hover:border-[#0F766E]/40 hover:text-[#14201E]' }}">
                {{ \Illuminate\Support\Str::headline($status) }}
            </a>
        @endforeach
    </nav>

    @php
        $statusDots = [
            'pending' => 'bg-[#B45309]',
            'confirmed' => 'bg-[#0F766E]',
            'completed' => 'bg-[#15803D]',
            'cancelled' => 'bg-[#465854]',
            'no_show' => 'bg-[#9F1239]',
        ];
    @endphp

    @if ($appointments->isNotEmpty())
        <ol class="relative space-y-4 before:absolute before:inset-y-4 before:left-[15px] before:w-px before:bg-[#DCE3E1] before:content-['']">
            @foreach ($appointments as $appointment)
                <li class="relative flex gap-4">
                    <span class="relative z-10 mt-6 flex h-8 w-8 shrink-0 items-center justify-center rounded-full ring-4 ring-[#F4F7F6] {{ $statusDots[$appointment->status] ?? 'bg-[#465854]' }}">
                        <x-icon name="calendar" class="h-4 w-4 text-white" />
                    </span>

                    <article class="card min-w-0 flex-1">
                        <div class="card-body py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="cs-num text-sm font-semibold text-[#14201E]">
                                            {{ date('g:i A', strtotime($appointment->start_time)) }}
                                            @if ($appointment->end_time)
                                                <span class="font-normal text-[#465854]">– {{ date('g:i A', strtotime($appointment->end_time)) }}</span>
                                            @endif
                                        </p>
                                        <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                                        <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                                    </div>

                                    <p class="cs-num mt-1 text-xs text-[#465854]">{{ $appointment->appointment_date->isoFormat('dddd, MMM D, YYYY') }}</p>

                                    <div class="mt-2.5 flex flex-wrap items-center gap-3">
                                        <span class="flex items-center gap-2 text-sm text-[#14201E]">
                                            @if ($appointment->physician?->user?->name)
                                                <x-avatar :name="$appointment->physician->user->name" size="sm" />
                                                Dr. {{ $appointment->physician->user->name }}
                                            @else
                                                Physician to be assigned
                                            @endif
                                        </span>
                                        <span class="chip cs-num font-mono text-xs">
                                            <x-icon name="list" class="h-3.5 w-3.5 text-[#465854]" />
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
