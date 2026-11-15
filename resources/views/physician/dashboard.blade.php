@extends('layouts.app')

@section('title', "Today's consultation queue")

@section('content')
    <x-page-header title="Today's consultation queue"
                   description="{{ $physician->physician_number }} · {{ \Illuminate\Support\Str::headline($physician->specialization) }} · {{ $physician->appointment_slot_minutes }}-minute consultation slots">
        <x-slot:actions>
            <a href="{{ route('physician.availability.index') }}" class="btn btn-secondary">
                <x-icon name="calendar" class="h-4 w-4" />
                My availability
            </a>
            <a href="{{ route('physician.encounters.index') }}" class="btn btn-secondary">
                <x-icon name="clipboard" class="h-4 w-4" />
                Encounter log
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Live caseload stats, next patient spotlight, and today's queue (aria-live on persistent container) --}}
    <div id="live-physician-queue"
         data-poll
         data-poll-url="{{ route('physician.dashboard') }}"
         data-poll-interval="30000"
         aria-live="polite">
        @include('physician.partials.board')
    </div>

    {{-- Upcoming appointments --}}
    <div class="card mt-6">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold tracking-tight text-[#14201E]">Upcoming appointments</h2>
                <p class="mt-0.5 text-xs text-[#465854]">Scheduled after today, with active status.</p>
            </div>
            <span class="chip cs-num">
                <x-icon name="calendar" class="h-3.5 w-3.5 text-[#465854]" />
                {{ $upcoming->count() }} upcoming
            </span>
        </div>

        {{-- Mobile stacked cards (below md) --}}
        <div class="divide-y divide-[#DCE3E1] md:hidden">
            @forelse ($upcoming as $appointment)
                <article class="space-y-2 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="cs-num text-xs font-semibold text-[#14201E]">
                            {{ $appointment->appointment_date->isoFormat('ddd, MMM D, YYYY') }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono text-xs font-bold text-[#0F766E] cs-num">#{{ $appointment->queue_number }}</span>
                            <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-avatar :name="$appointment->patient->user->name" size="sm" />
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-[#14201E]">{{ $appointment->patient->user->name }}</p>
                            <p class="cs-num text-xs text-[#465854]">
                                {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }}
                                · {{ $appointment->patient->patient_number }}
                            </p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="p-6">
                    <x-empty icon="calendar" title="No upcoming appointments"
                             description="No upcoming appointments scheduled." />
                </div>
            @endforelse
        </div>

        {{-- Desktop table (md and up) --}}
        <div class="hidden overflow-x-auto md:block">
            @if ($upcoming->isNotEmpty())
                <table class="w-full text-left">
                    <caption class="sr-only">Upcoming physician appointments</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="table-head">Date</th>
                            <th scope="col" class="table-head">Time</th>
                            <th scope="col" class="table-head">Patient</th>
                            <th scope="col" class="table-head">Queue #</th>
                            <th scope="col" class="table-head">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($upcoming as $appointment)
                        <tr class="table-row">
                            <td class="table-cell cs-num whitespace-nowrap">{{ $appointment->appointment_date->isoFormat('dddd, MMM D, YYYY') }}</td>
                            <td class="table-cell cs-num whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }}</td>
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$appointment->patient->user->name" size="sm" />
                                    <div class="min-w-0">
                                        <p class="font-medium text-[#14201E]">{{ $appointment->patient->user->name }}</p>
                                        <p class="font-mono text-xs text-[#465854]">{{ $appointment->patient->patient_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-cell cs-num font-mono font-semibold text-[#14201E]">#{{ $appointment->queue_number }}</td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-8">
                    <x-empty icon="calendar" title="No upcoming appointments"
                             description="No upcoming appointments scheduled." />
                </div>
            @endif
        </div>
    </div>
@endsection
