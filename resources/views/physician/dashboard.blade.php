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

    {{-- Live caseload stats, next patient spotlight, and today's queue --}}
    <div id="live-physician-queue" data-poll data-poll-url="{{ route('physician.dashboard') }}" data-poll-interval="30000">
        @include('physician.partials.board')
    </div>

    {{-- Upcoming appointments --}}
    <div class="mt-6 card">
        <div class="card-header flex items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold tracking-tight text-slate-900">Upcoming appointments</h3>
                <p class="mt-0.5 text-xs text-slate-500">Scheduled after today, with active status.</p>
            </div>
            <span class="chip">
                <x-icon name="calendar" class="h-3.5 w-3.5 text-slate-400" />
                {{ $upcoming->count() }} upcoming
            </span>
        </div>

        {{-- Mobile stacked cards (below md) --}}
        <div class="divide-y divide-slate-100 md:hidden">
            @forelse ($upcoming as $appointment)
                <article class="p-4 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-slate-900">
                            {{ $appointment->appointment_date->isoFormat('ddd, MMM D, YYYY') }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-700">#{{ $appointment->queue_number }}</span>
                            <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-avatar :name="$appointment->patient->user->name" size="sm" />
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900">{{ $appointment->patient->user->name }}</p>
                            <p class="text-xs text-slate-500">
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
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th class="table-head">Date</th>
                        <th class="table-head">Time</th>
                        <th class="table-head">Patient</th>
                        <th class="table-head">Queue #</th>
                        <th class="table-head">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($upcoming as $appointment)
                        <tr class="table-row">
                            <td class="table-cell whitespace-nowrap">{{ $appointment->appointment_date->isoFormat('dddd, MMM D, YYYY') }}</td>
                            <td class="table-cell whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }}</td>
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$appointment->patient->user->name" size="sm" />
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-900">{{ $appointment->patient->user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $appointment->patient->patient_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-cell font-semibold text-slate-900">#{{ $appointment->queue_number }}</td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12">
                                <x-empty icon="calendar" title="No upcoming appointments"
                                         description="No upcoming appointments scheduled." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
