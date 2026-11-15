@extends('layouts.app')

@section('title', 'System overview')

@section('content')
    <x-page-header title="System overview" description="Clinic operations, 7-day booking volume, readiness checks and recent audit activity.">
        <x-slot:actions>
            <a href="{{ route('admin.queue.index') }}" class="btn btn-secondary">
                <x-icon name="list" class="h-4 w-4" />
                Daily queue
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-primary">
                <x-icon name="chart-bar" class="h-4 w-4" />
                Daily census
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Today's live clinic pulse --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Booked today" :value="$todayTotal" icon="calendar" tone="teal" />
        <x-stat label="Waiting in queue" :value="$todayWaiting" icon="clock" tone="amber" />
        <x-stat label="Completed visits" :value="$todayCompleted" icon="check-circle" tone="emerald" />
        <x-stat label="Encounters today" :value="$todayEncounters" icon="clipboard" tone="indigo" />
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        {{-- 7-day appointment trend --}}
        <section class="card lg:col-span-2">
            <div class="card-header flex items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold tracking-tight text-slate-900">7-day appointment volume</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Total bookings, completed consultations and no-shows per day.</p>
                </div>
                <span class="chip">Last 7 days</span>
            </div>
            <div class="card-body space-y-3">
                @foreach ($trends as $day)
                    @php
                        $widthPct = (int) round(($day['total'] / $maxTrendTotal) * 100);
                    @endphp
                    <div class="flex items-center gap-4 text-xs">
                        <div class="w-20 shrink-0">
                            <span class="font-semibold text-slate-800">{{ $day['label'] }}</span>
                            <span class="ml-1 text-slate-400">{{ $day['dateLabel'] }}</span>
                        </div>
                        <div class="h-3 flex-1 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-teal-600 transition-all" style="width: {{ $widthPct }}%"></div>
                        </div>
                        <div class="flex w-36 shrink-0 items-center justify-end gap-3 tabular-nums">
                            <span class="font-semibold text-slate-900">{{ $day['total'] }} total</span>
                            <span class="text-emerald-700">{{ $day['completed'] }} done</span>
                            @if ($day['noShow'] > 0)
                                <span class="text-rose-600">{{ $day['noShow'] }} NS</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Governance & clinic readiness --}}
        <section class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold tracking-tight text-slate-900">Clinic readiness</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3">
                        <p class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Active users</p>
                        <p class="mt-1 text-xl font-bold text-slate-900 tabular-nums">{{ $activeUsers }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3">
                        <p class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Patients</p>
                        <p class="mt-1 text-xl font-bold text-slate-900 tabular-nums">{{ $totalPatients }}</p>
                    </div>
                </div>

                <div class="space-y-2 border-t border-slate-100 pt-3 text-xs">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-600">Physicians without active schedules</span>
                        @if ($unscheduledPhysicians->isEmpty())
                            <span class="badge badge-completed">All scheduled</span>
                        @else
                            <a href="{{ route('admin.schedules.index') }}" class="badge badge-pending hover:underline">
                                {{ $unscheduledPhysicians->count() }} unscheduled
                            </a>
                        @endif
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-600">Suspended user accounts</span>
                        @if ($suspendedUsers === 0)
                            <span class="badge badge-enabled">None</span>
                        @else
                            <a href="{{ route('admin.users.index') }}" class="badge badge-suspended hover:underline">
                                {{ $suspendedUsers }} suspended
                            </a>
                        @endif
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-600">Active physicians</span>
                        <span class="font-semibold text-slate-900">{{ $totalPhysicians }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Recent audit events --}}
    <section class="card">
        <div class="card-header flex items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold tracking-tight text-slate-900">Recent audit activity</h3>
                <p class="mt-0.5 text-xs text-slate-500">Latest security and clinical accountability events.</p>
            </div>
            <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-semibold text-teal-600 hover:text-teal-700">
                Full audit log &rarr;
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="table-head">Timestamp</th>
                        <th class="table-head">Actor</th>
                        <th class="table-head">Action</th>
                        <th class="table-head">Target</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLogs as $log)
                        <tr class="table-row">
                            <td class="table-cell whitespace-nowrap text-xs text-slate-500">
                                {{ $log->created_at?->isoFormat('MMM D, h:mm A') ?? '—' }}
                            </td>
                            <td class="table-cell font-medium text-slate-900">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="table-cell">
                                <span class="font-mono text-xs text-teal-800">{{ $log->action }}</span>
                            </td>
                            <td class="table-cell text-xs text-slate-500">
                                {{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">No audit events recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
