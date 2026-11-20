@extends('layouts.app')

@section('title', 'Daily census')

@section('content')
    <div class="print-only mb-5 border-b border-hairline pb-3">
        <p class="font-mono text-[10px] font-semibold tracking-wider text-meta uppercase">Camiguin Polytechnic State College · Outpatient Clinic</p>
        <p class="mt-0.5 text-base font-bold text-bright">Daily Census Report — {{ \Illuminate\Support\Carbon::parse($date)->isoFormat('dddd, MMM D, YYYY') }}</p>
    </div>

    <x-page-header title="Daily census" description="Appointments, consultations and workload for the selected clinic day.">
        <x-slot:actions>
            <form method="GET" action="{{ route('admin.reports.index') }}" class="no-print flex flex-wrap items-end gap-2.5">
                <div>
                    <label for="report-date" class="sr-only">Report date</label>
                    <input id="report-date" type="date" name="date" value="{{ $date }}" class="input cs-num">
                </div>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" class="h-4 w-4" />
                    Apply
                </button>
                @if ($date !== now()->toDateString())
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-ghost text-brand">
                        Today
                    </a>
                @endif
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

    {{-- Balanced 4 + 3 dossier KPI strips (Visit Volume vs Clinic Standing) --}}
    <div class="mb-6 grid gap-4 xl:grid-cols-12">
        <div class="ledger-strip sm:grid-cols-4 xl:col-span-7">
            <div class="ledger-cell">
                <p class="stat-label">Total appointments</p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-brand cs-num">{{ $appointments->count() }}</p>
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
        </div>
        <div class="ledger-strip sm:grid-cols-3 xl:col-span-5">
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
                                <td class="table-cell cs-num font-mono text-right font-semibold text-bright">{{ $count }}</td>
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
            <div class="divide-y divide-hairline md:hidden">
                @forelse ($byPhysician as $row)
                    <article class="space-y-2 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-bright">
                                {{ $row['physician'] ? 'Dr. '.$row['physician']->user->name : 'Unassigned' }}
                            </p>
                            <span class="chip cs-num font-mono text-[11px]">{{ $row['total'] }} total</span>
                        </div>
                        <div class="grid grid-cols-5 divide-x divide-hairline rounded-md border border-hairline bg-canvas text-center text-xs cs-num">
                            <div class="py-1.5">
                                <p class="font-mono text-[10px] text-meta">Pend</p>
                                <p class="font-semibold text-bright">{{ $row['pending'] }}</p>
                            </div>
                            <div class="py-1.5">
                                <p class="font-mono text-[10px] text-meta">Conf</p>
                                <p class="font-semibold text-bright">{{ $row['confirmed'] }}</p>
                            </div>
                            <div class="py-1.5">
                                <p class="font-mono text-[10px] text-meta">Done</p>
                                <p class="font-semibold text-ok">{{ $row['completed'] }}</p>
                            </div>
                            <div class="py-1.5">
                                <p class="font-mono text-[10px] text-meta">Canc</p>
                                <p class="font-semibold text-bright">{{ $row['cancelled'] }}</p>
                            </div>
                            <div class="py-1.5">
                                <p class="font-mono text-[10px] text-meta">NS</p>
                                <p class="font-semibold text-danger">{{ $row['no_show'] }}</p>
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
                                <td class="table-cell font-medium text-bright">
                                    {{ $row['physician'] ? 'Dr. '.$row['physician']->user->name : 'Unassigned' }}
                                </td>
                                <td class="table-cell cs-num font-mono text-right font-semibold text-bright">{{ $row['total'] }}</td>
                                <td class="table-cell cs-num font-mono text-right">{{ $row['pending'] }}</td>
                                <td class="table-cell cs-num font-mono text-right">{{ $row['confirmed'] }}</td>
                                <td class="table-cell cs-num font-mono text-right text-ok">{{ $row['completed'] }}</td>
                                <td class="table-cell cs-num font-mono text-right text-meta">{{ $row['cancelled'] }}</td>
                                <td class="table-cell cs-num font-mono text-right text-danger">{{ $row['no_show'] }}</td>
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
            <span class="font-mono text-xs text-meta cs-num">{{ $encounters->count() }} encounter(s)</span>
        </div>

        <div class="divide-y divide-hairline md:hidden">
            @forelse ($encounters as $encounter)
                <article class="space-y-1.5 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-semibold text-brand">{{ $encounter->encounter_number }}</span>
                        <span class="cs-num font-mono text-xs text-meta">{{ $encounter->encountered_at->format('g:i A') }}</span>
                    </div>
                    <p class="text-sm font-semibold text-bright">{{ $encounter->patient->user->name }}</p>
                    <p class="text-xs text-meta">Dr. {{ $encounter->physician->user->name }}</p>
                    @if ($encounter->diagnosis)
                        <p class="text-xs text-read">Diagnosis: {{ $encounter->diagnosis }}</p>
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
                            <td class="table-cell font-mono text-xs font-semibold text-brand">{{ $encounter->encounter_number }}</td>
                            <td class="table-cell cs-num font-mono text-xs whitespace-nowrap">{{ $encounter->encountered_at->format('g:i A') }}</td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2.5">
                                    <x-avatar :name="$encounter->patient->user->name" size="sm" />
                                    <span class="font-medium text-bright">{{ $encounter->patient->user->name }}</span>
                                </div>
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2.5">
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
            <span class="font-mono text-xs text-meta cs-num">{{ $appointments->count() }} appointment(s)</span>
        </div>

        <div class="divide-y divide-hairline md:hidden">
            @forelse ($appointments as $appointment)
                <article class="space-y-2 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-brand cs-num">#{{ $appointment->queue_number }}</span>
                        <div class="flex items-center gap-1.5">
                            <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                            <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                        </div>
                    </div>
                    <p class="text-sm font-semibold text-bright">{{ $appointment->patient->user->name }}</p>
                    <p class="text-xs text-meta cs-num">
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
                            <td class="table-cell cs-num font-mono font-semibold text-brand">#{{ $appointment->queue_number }}</td>
                            <td class="table-cell cs-num font-mono text-xs whitespace-nowrap">
                                {{ date('g:i A', strtotime($appointment->start_time)) }} – {{ date('g:i A', strtotime($appointment->end_time)) }}
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2.5">
                                    <x-avatar :name="$appointment->patient->user->name" size="sm" />
                                    <span class="font-medium text-bright">{{ $appointment->patient->user->name }}</span>
                                </div>
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2.5">
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
