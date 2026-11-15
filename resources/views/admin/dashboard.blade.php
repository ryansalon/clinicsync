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

    {{-- Today's live clinic pulse (single hairline ledger strip) --}}
    <div class="ledger-strip mb-6 lg:grid-cols-4">
        <div class="ledger-cell">
            <div class="flex items-start justify-between gap-2">
                <p class="stat-label">Booked today</p>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-[#DCE3E1] bg-[#EFF4F2] text-[#465854]">
                    <x-icon name="calendar" class="h-4 w-4" />
                </span>
            </div>
            <div>
                <p class="text-2xl font-semibold tracking-tight text-[#14201E] cs-num">{{ $todayTotal }}</p>
                <p class="mt-0.5 text-xs text-[#465854]">Scheduled + walk-ins</p>
            </div>
        </div>
        <a href="{{ route('admin.queue.index') }}" class="ledger-cell group hover:bg-[#F4F7F6]">
            <div class="flex items-start justify-between gap-2">
                <p class="stat-label">Waiting in queue</p>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-amber-200 bg-amber-50 text-amber-800">
                    <x-icon name="clock" class="h-4 w-4" />
                </span>
            </div>
            <div class="flex items-end justify-between gap-2">
                <div>
                    <p class="text-2xl font-semibold tracking-tight text-amber-900 cs-num">{{ $todayWaiting }}</p>
                    <p class="mt-0.5 text-xs text-[#465854]">Awaiting call or triage</p>
                </div>
                <span class="text-xs font-medium text-[#0F766E] opacity-0 transition group-hover:opacity-100">Queue &rarr;</span>
            </div>
        </a>
        <div class="ledger-cell">
            <div class="flex items-start justify-between gap-2">
                <p class="stat-label">Completed visits</p>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800">
                    <x-icon name="check-circle" class="h-4 w-4" />
                </span>
            </div>
            <div>
                <p class="text-2xl font-semibold tracking-tight text-emerald-800 cs-num">{{ $todayCompleted }}</p>
                <p class="mt-0.5 text-xs text-[#465854]">Consultations closed</p>
            </div>
        </div>
        <div class="ledger-cell">
            <div class="flex items-start justify-between gap-2">
                <p class="stat-label">Encounters today</p>
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-[#B5DCD4] bg-[#E6F4F1] text-[#0F766E]">
                    <x-icon name="clipboard" class="h-4 w-4" />
                </span>
            </div>
            <div>
                <p class="text-2xl font-semibold tracking-tight text-[#0F766E] cs-num">{{ $todayEncounters }}</p>
                <p class="mt-0.5 text-xs text-[#465854]">Chart notes signed</p>
            </div>
        </div>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        {{-- 7-day appointment trend --}}
        <section class="card lg:col-span-2">
            <div class="card-header flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-[#14201E]">7-day appointment volume</h2>
                    <p class="mt-0.5 text-xs text-[#465854]">Total bookings, completed consultations and no-shows per day.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-[11px] font-medium text-[#465854]">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-[#0F766E]" aria-hidden="true"></span>
                        Done
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-[#84BEB5]" aria-hidden="true"></span>
                        Scheduled
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-rose-600" aria-hidden="true"></span>
                        No show
                    </span>
                </div>
            </div>
            <div class="card-body space-y-3.5">
                @foreach ($trends as $day)
                    @php
                        $divisor = max($maxTrendTotal, 1);
                        $completedPct = (int) round(($day['completed'] / $divisor) * 100);
                        $noShowPct = (int) round(($day['noShow'] / $divisor) * 100);
                        $activeRemaining = max(0, $day['total'] - $day['completed'] - $day['noShow']);
                        $activePct = (int) round(($activeRemaining / $divisor) * 100);
                    @endphp
                    <div class="flex flex-col gap-1.5 text-xs sm:flex-row sm:items-center sm:gap-4">
                        <div class="flex items-center justify-between sm:w-24 sm:shrink-0 sm:justify-start">
                            <span class="font-semibold text-[#14201E]">{{ $day['label'] }}</span>
                            <span class="ml-1.5 font-mono text-[#465854] cs-num">{{ $day['dateLabel'] }}</span>
                        </div>
                        <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-[#EFF4F2] sm:flex-1"
                             title="{{ $day['total'] }} total · {{ $day['completed'] }} completed · {{ $day['noShow'] }} no-show">
                            @if ($completedPct > 0)
                                <div class="h-full bg-[#0F766E] transition-all" style="width: {{ $completedPct }}%"></div>
                            @endif
                            @if ($activePct > 0)
                                <div class="h-full bg-[#84BEB5] transition-all" style="width: {{ $activePct }}%"></div>
                            @endif
                            @if ($noShowPct > 0)
                                <div class="h-full bg-rose-600 transition-all" style="width: {{ $noShowPct }}%"></div>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-3 cs-num sm:w-44 sm:shrink-0 sm:justify-end">
                            <span class="font-semibold text-[#14201E]">{{ $day['total'] }} total</span>
                            <span class="text-[#0F766E]">{{ $day['completed'] }} done</span>
                            @if ($day['noShow'] > 0)
                                <span class="text-rose-800">{{ $day['noShow'] }} no-show</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Governance & clinic readiness (flat hairline rules, zero nested card boxes) --}}
        <section class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold tracking-tight text-[#14201E]">Clinic readiness</h2>
                <p class="mt-0.5 text-xs text-[#465854]">Staffing coverage and user access standing.</p>
            </div>
            <div class="divide-y divide-[#DCE3E1]">
                <div class="grid grid-cols-2 divide-x divide-[#DCE3E1]">
                    <div class="px-5 py-4">
                        <p class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Active users</p>
                        <p class="mt-1 text-2xl font-semibold text-[#14201E] cs-num">{{ $activeUsers }}</p>
                    </div>
                    <div class="px-5 py-4">
                        <p class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Patients</p>
                        <p class="mt-1 text-2xl font-semibold text-[#14201E] cs-num">{{ $totalPatients }}</p>
                    </div>
                </div>

                <div class="divide-y divide-[#DCE3E1] text-xs">
                    <a href="{{ route('admin.schedules.index') }}" class="flex items-center justify-between gap-2 px-5 py-3 transition hover:bg-[#F4F7F6]">
                        <span class="font-medium text-[#243330]">Physicians without schedules</span>
                        @if ($unscheduledPhysicians->isEmpty())
                            <span class="badge badge-completed">All scheduled</span>
                        @else
                            <span class="badge badge-pending">{{ $unscheduledPhysicians->count() }} unscheduled</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between gap-2 px-5 py-3 transition hover:bg-[#F4F7F6]">
                        <span class="font-medium text-[#243330]">Suspended user accounts</span>
                        @if ($suspendedUsers === 0)
                            <span class="badge badge-enabled">None</span>
                        @else
                            <span class="badge badge-suspended">{{ $suspendedUsers }} suspended</span>
                        @endif
                    </a>
                    <div class="flex items-center justify-between gap-2 px-5 py-3">
                        <span class="font-medium text-[#243330]">Active physicians</span>
                        <span class="font-mono font-semibold text-[#14201E] cs-num">{{ $totalPhysicians }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Recent audit events --}}
    <section class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold tracking-tight text-[#14201E]">Recent audit activity</h2>
                <p class="mt-0.5 text-xs text-[#465854]">Latest security and clinical accountability events.</p>
            </div>
            <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#0F766E] hover:underline">
                Full audit log &rarr;
            </a>
        </div>
        @if ($recentLogs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <caption class="sr-only">Recent clinic audit log events</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="table-head">Timestamp</th>
                            <th scope="col" class="table-head">Actor</th>
                            <th scope="col" class="table-head">Action</th>
                            <th scope="col" class="table-head">Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentLogs as $log)
                            <tr class="table-row">
                                <td class="table-cell cs-num whitespace-nowrap font-mono text-xs text-[#465854]">
                                    {{ $log->created_at?->isoFormat('MMM D, h:mm A') ?? '—' }}
                                </td>
                                <td class="table-cell">
                                    <div class="flex items-center gap-2.5">
                                        <x-avatar :name="$log->user?->name ?? 'System'" size="sm" />
                                        <span class="font-medium text-[#14201E]">{{ $log->user?->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <code class="rounded border border-[#DCE3E1] bg-[#EFF4F2] px-2 py-0.5 font-mono text-xs text-[#0F766E]">{{ $log->action }}</code>
                                </td>
                                <td class="table-cell cs-num text-xs text-[#465854]">
                                    {{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8">
                <x-empty icon="shield-check" title="No audit events recorded yet"
                         description="Account updates, queue transitions, and encounter records appear automatically as staff work." />
            </div>
        @endif
    </section>
@endsection
