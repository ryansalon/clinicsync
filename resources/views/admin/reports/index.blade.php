@extends('layouts.app')

@section('title', 'Daily census')

@section('content')
    <x-page-header title="Daily census" description="Appointments, consultations and workload for the selected clinic day.">
        <x-slot:actions>
            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="report-date" class="label">Date</label>
                    <input id="report-date" type="date" name="date" value="{{ $date }}" class="input">
                </div>
                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" class="h-4 w-4" />
                    Apply
                </button>
            </form>

            <button type="button" class="btn btn-secondary" onclick="window.print()">
                <x-icon name="document" class="h-4 w-4" />
                Print
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- Key figures --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-7">
        <x-stat label="Total appointments" :value="$appointments->count()" icon="calendar" tone="teal" />
        <x-stat label="Online bookings" value="{{ $online }}" icon="list" tone="sky" />
        <x-stat label="Walk-ins" value="{{ $walkIns }}" icon="user-plus" tone="violet" />
        <x-stat label="Consultations" :value="$encounters->count()" icon="clipboard-check" tone="indigo" />
        <x-stat label="Registered patients" value="{{ $registeredPatients }}" icon="users" tone="emerald" />
        <x-stat label="Active accounts" value="{{ $activeAccounts }}" icon="user" tone="amber" />
        <x-stat label="Physicians" value="{{ $physicianCount }}" icon="heart-pulse" tone="rose" />
    </div>

    {{-- Status breakdown + physician load --}}
    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        <div class="card">
            <div class="card-header">
                <h3 class="section-title">Breakdown by status</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="table-head">Status</th>
                            <th class="table-head text-right">Appointments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byStatus as $status => $count)
                            <tr class="table-row">
                                <td class="table-cell">
                                    <span class="badge badge-{{ $status }}">{{ \Illuminate\Support\Str::headline($status) }}</span>
                                </td>
                                <td class="table-cell text-right font-semibold text-slate-900">{{ $count }}</td>
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
                <h3 class="section-title">Load by physician</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="table-head">Physician</th>
                            <th class="table-head text-right">Total</th>
                            <th class="table-head text-right">Pending</th>
                            <th class="table-head text-right">Confirmed</th>
                            <th class="table-head text-right">Completed</th>
                            <th class="table-head text-right">Cancelled</th>
                            <th class="table-head text-right">No show</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byPhysician as $row)
                            <tr class="table-row">
                                <td class="table-cell font-medium text-slate-900">
                                    {{ $row['physician'] ? 'Dr. '.$row['physician']->user->name : 'Unassigned' }}
                                </td>
                                <td class="table-cell text-right font-semibold text-slate-900">{{ $row['total'] }}</td>
                                <td class="table-cell text-right">{{ $row['pending'] }}</td>
                                <td class="table-cell text-right">{{ $row['confirmed'] }}</td>
                                <td class="table-cell text-right">{{ $row['completed'] }}</td>
                                <td class="table-cell text-right">{{ $row['cancelled'] }}</td>
                                <td class="table-cell text-right">{{ $row['no_show'] }}</td>
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
            <h3 class="section-title">Consultations recorded</h3>
            <span class="text-xs text-slate-500">{{ $encounters->count() }} encounter(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="table-head">Encounter #</th>
                        <th class="table-head">Time</th>
                        <th class="table-head">Patient</th>
                        <th class="table-head">Physician</th>
                        <th class="table-head">Diagnosis</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($encounters as $encounter)
                        <tr class="table-row">
                            <td class="table-cell font-medium text-slate-900">{{ $encounter->encounter_number }}</td>
                            <td class="table-cell whitespace-nowrap">{{ $encounter->encountered_at->format('g:i A') }}</td>
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
            <h3 class="section-title">Queue listing</h3>
            <span class="text-xs text-slate-500">{{ $appointments->count() }} appointment(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="table-head">Queue #</th>
                        <th class="table-head">Time</th>
                        <th class="table-head">Patient</th>
                        <th class="table-head">Physician</th>
                        <th class="table-head">Source</th>
                        <th class="table-head">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appointment)
                        <tr class="table-row">
                            <td class="table-cell font-semibold text-slate-900">#{{ $appointment->queue_number }}</td>
                            <td class="table-cell whitespace-nowrap">
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
