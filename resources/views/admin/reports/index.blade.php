@extends('layouts.app')

@section('title', 'Daily census')

@section('content')
    <x-page-header title="Daily census" description="Appointments, consultations and workload for the selected clinic day.">
        <x-slot:actions>
            <form method="GET" action="{{ route('admin.reports.index') }}" class="no-print flex flex-wrap items-end gap-3">
                <div>
                    <label for="report-date" class="label">Date</label>
                    <input id="report-date" type="date" name="date" value="{{ $date }}" class="input cs-num">
                </div>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" class="h-4 w-4" />
                    Apply
                </button>
            </form>

            <a href="{{ route('admin.reports.export', ['date' => $date]) }}" class="btn btn-secondary no-print">
                <x-icon name="arrow-right" class="h-4 w-4" />
                Export CSV
            </a>

            <button type="button" class="btn btn-secondary no-print" onclick="window.print()">
                <x-icon name="document" class="h-4 w-4" />
                Print
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- Disciplined hairline ledger replacing 7-hue KPI rainbow --}}
    <div class="ledger-strip mb-6 sm:grid-cols-4 xl:grid-cols-7">
        <div class="ledger-cell">
            <p class="stat-label">Total appointments</p>
            <p class="mt-1 text-2xl font-semibold tracking-tight text-[#0F766E] cs-num">{{ $appointments->count() }}</p>
        </div>
        <div class="ledger-cell">
            <p class="stat-label">Online bookings</p>
            <p class="stat-value">{{ $online }}</p>
        </div>
        <div class="ledger-cell">
            <p class="stat-label">Walk-ins</p>
            <p class="stat-value">{{ $walkIns }}</p>
        </div>
        <div class="ledger-cell">
            <p class="stat-label">Consultations</p>
            <p class="stat-value">{{ $encounters->count() }}</p>
        </div>
        <div class="ledger-cell">
            <p class="stat-label">Registered patients</p>
            <p class="stat-value">{{ $registeredPatients }}</p>
        </div>
        <div class="ledger-cell">
            <p class="stat-label">Active accounts</p>
            <p class="stat-value">{{ $activeAccounts }}</p>
        </div>
        <div class="ledger-cell col-span-2 sm:col-span-1">
            <p class="stat-label">Physicians</p>
            <p class="stat-value">{{ $physicianCount }}</p>
        </div>
    </div>

    {{-- Status breakdown + physician load --}}
    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        <div class="card">
            <div class="card-header">
                <h2 class="section-title">Breakdown by status</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <caption class="sr-only">Daily census breakdown by appointment status</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="table-head">Status</th>
                            <th scope="col" class="table-head text-right">Appointments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byStatus as $status => $count)
                            <tr class="table-row">
                                <td class="table-cell">
                                    <span class="badge badge-{{ $status }}">{{ \Illuminate\Support\Str::headline($status) }}</span>
                                </td>
                                <td class="table-cell cs-num text-right font-semibold text-[#14201E]">{{ $count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-12">
                                    <x-empty icon="chart-bar" title="No status data"
                                             description="No appointments were recorded for this date." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card lg:col-span-2">
            <div class="card-header">
                <h2 class="section-title">Load by physician</h2>
            </div>

            {{-- Mobile load cards --}}
            <div class="divide-y divide-[#DCE3E1] md:hidden">
                @forelse ($byPhysician as $row)
                    <article class="space-y-2 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-medium text-[#14201E]">
                                {{ $row['physician'] ? 'Dr. '.$row['physician']->user->name : 'Unassigned' }}
                            </p>
                            <span class="chip cs-num">{{ $row['total'] }} total</span>
                        </div>
                        <div class="grid grid-cols-5 gap-1.5 text-center text-xs cs-num">
                            <div class="rounded border border-[#DCE3E1] bg-[#F4F7F6] py-1">
                                <p class="text-[10px] text-[#465854]">Pend</p>
                                <p class="font-semibold text-[#14201E]">{{ $row['pending'] }}</p>
                            </div>
                            <div class="rounded border border-[#DCE3E1] bg-[#F4F7F6] py-1">
                                <p class="text-[10px] text-[#465854]">Conf</p>
                                <p class="font-semibold text-[#14201E]">{{ $row['confirmed'] }}</p>
                            </div>
                            <div class="rounded border border-[#DCE3E1] bg-[#F4F7F6] py-1">
                                <p class="text-[10px] text-[#465854]">Done</p>
                                <p class="font-semibold text-[#0F766E]">{{ $row['completed'] }}</p>
                            </div>
                            <div class="rounded border border-[#DCE3E1] bg-[#F4F7F6] py-1">
                                <p class="text-[10px] text-[#465854]">Canc</p>
                                <p class="font-semibold text-[#14201E]">{{ $row['cancelled'] }}</p>
                            </div>
                            <div class="rounded border border-[#DCE3E1] bg-[#F4F7F6] py-1">
                                <p class="text-[10px] text-[#465854]">NS</p>
                                <p class="font-semibold text-rose-800">{{ $row['no_show'] }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="p-8">
                        <x-empty icon="chart-bar" title="No physician load"
                                 description="No appointments were recorded for this date." />
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full">
                    <caption class="sr-only">Daily clinic workload by physician</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="table-head">Physician</th>
                            <th scope="col" class="table-head text-right">Total</th>
                            <th scope="col" class="table-head text-right">Pending</th>
                            <th scope="col" class="table-head text-right">Confirmed</th>
                            <th scope="col" class="table-head text-right">Completed</th>
                            <th scope="col" class="table-head text-right">Cancelled</th>
                            <th scope="col" class="table-head text-right">No show</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byPhysician as $row)
                            <tr class="table-row">
                                <td class="table-cell font-medium text-[#14201E]">
                                    {{ $row['physician'] ? 'Dr. '.$row['physician']->user->name : 'Unassigned' }}
                                </td>
                                <td class="table-cell cs-num text-right font-semibold text-[#14201E]">{{ $row['total'] }}</td>
                                <td class="table-cell cs-num text-right">{{ $row['pending'] }}</td>
                                <td class="table-cell cs-num text-right">{{ $row['confirmed'] }}</td>
                                <td class="table-cell cs-num text-right">{{ $row['completed'] }}</td>
                                <td class="table-cell cs-num text-right">{{ $row['cancelled'] }}</td>
                                <td class="table-cell cs-num text-right">{{ $row['no_show'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12">
                                    <x-empty icon="chart-bar" title="No physician load"
                                             description="No appointments were recorded for this date." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Consultations --}}
    <div class="card mb-6">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <h2 class="section-title">Consultations recorded</h2>
            <span class="text-xs text-[#465854] cs-num">{{ $encounters->count() }} encounter(s)</span>
        </div>

        <div class="divide-y divide-[#DCE3E1] md:hidden">
            @forelse ($encounters as $encounter)
                <article class="space-y-1.5 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-semibold text-[#0F766E]">{{ $encounter->encounter_number }}</span>
                        <span class="cs-num text-xs text-[#465854]">{{ $encounter->encountered_at->format('g:i A') }}</span>
                    </div>
                    <p class="text-sm font-medium text-[#14201E]">{{ $encounter->patient->user->name }}</p>
                    <p class="text-xs text-[#465854]">Dr. {{ $encounter->physician->user->name }}</p>
                    @if ($encounter->diagnosis)
                        <p class="text-xs text-[#243330]">Diagnosis: {{ $encounter->diagnosis }}</p>
                    @endif
                </article>
            @empty
                <div class="p-8">
                    <x-empty icon="clipboard" title="No consultations recorded"
                             description="No consultations were recorded for this date." />
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full">
                <caption class="sr-only">Consultations recorded on {{ $date }}</caption>
                <thead>
                    <tr>
                        <th scope="col" class="table-head">Encounter #</th>
                        <th scope="col" class="table-head">Time</th>
                        <th scope="col" class="table-head">Patient</th>
                        <th scope="col" class="table-head">Physician</th>
                        <th scope="col" class="table-head">Diagnosis</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($encounters as $encounter)
                        <tr class="table-row">
                            <td class="table-cell font-mono text-xs font-semibold text-[#14201E]">{{ $encounter->encounter_number }}</td>
                            <td class="table-cell cs-num whitespace-nowrap">{{ $encounter->encountered_at->format('g:i A') }}</td>
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$encounter->patient->user->name" size="sm" />
                                    <span>{{ $encounter->patient->user->name }}</span>
                                </div>
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$encounter->physician->user->name" size="sm" />
                                    <span>Dr. {{ $encounter->physician->user->name }}</span>
                                </div>
                            </td>
                            <td class="table-cell">{{ $encounter->diagnosis ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12">
                                <x-empty icon="clipboard" title="No consultations recorded"
                                         description="No consultations were recorded for this date." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Queue listing --}}
    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <h2 class="section-title">Queue listing</h2>
            <span class="text-xs text-[#465854] cs-num">{{ $appointments->count() }} appointment(s)</span>
        </div>

        <div class="divide-y divide-[#DCE3E1] md:hidden">
            @forelse ($appointments as $appointment)
                <article class="space-y-2 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-[#0F766E] cs-num">#{{ $appointment->queue_number }}</span>
                        <div class="flex items-center gap-1.5">
                            <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                            <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                        </div>
                    </div>
                    <p class="text-sm font-medium text-[#14201E]">{{ $appointment->patient->user->name }}</p>
                    <p class="text-xs text-[#465854] cs-num">
                        Dr. {{ $appointment->physician->user->name }} ·
                        {{ date('g:i A', strtotime($appointment->start_time)) }} – {{ date('g:i A', strtotime($appointment->end_time)) }}
                    </p>
                </article>
            @empty
                <div class="p-8">
                    <x-empty icon="list" title="No appointments scheduled"
                             description="No appointments are scheduled for this date." />
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full">
                <caption class="sr-only">Queue listing for {{ $date }}</caption>
                <thead>
                    <tr>
                        <th scope="col" class="table-head">Queue #</th>
                        <th scope="col" class="table-head">Time</th>
                        <th scope="col" class="table-head">Patient</th>
                        <th scope="col" class="table-head">Physician</th>
                        <th scope="col" class="table-head">Source</th>
                        <th scope="col" class="table-head">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appointment)
                        <tr class="table-row">
                            <td class="table-cell cs-num font-mono font-semibold text-[#14201E]">#{{ $appointment->queue_number }}</td>
                            <td class="table-cell cs-num whitespace-nowrap">
                                {{ date('g:i A', strtotime($appointment->start_time)) }} – {{ date('g:i A', strtotime($appointment->end_time)) }}
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$appointment->patient->user->name" size="sm" />
                                    <span>{{ $appointment->patient->user->name }}</span>
                                </div>
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$appointment->physician->user->name" size="sm" />
                                    <span>Dr. {{ $appointment->physician->user->name }}</span>
                                </div>
                            </td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                            </td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12">
                                <x-empty icon="list" title="No appointments scheduled"
                                         description="No appointments are scheduled for this date." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
