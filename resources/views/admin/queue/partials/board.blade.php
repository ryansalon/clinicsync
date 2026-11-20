{{-- Compact hairline queue KPI ledger strip --}}
<div class="ledger-strip mb-4 lg:grid-cols-5">
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Waiting</p>
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-warn-border bg-warn-tint text-warn">
                <x-icon name="clock" class="h-3.5 w-3.5" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-bold tracking-tight text-warn cs-num">{{ $waiting->count() }}</p>
            <p class="mt-0.5 text-xs text-meta">In waiting line</p>
        </div>
    </div>
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Confirmed</p>
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-brand-border bg-brand-tint text-brand">
                <x-icon name="check" class="h-3.5 w-3.5" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-bold tracking-tight text-brand cs-num">{{ $confirmed }}</p>
            <p class="mt-0.5 text-xs text-meta">Checked in / ready</p>
        </div>
    </div>
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Completed</p>
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-ok-border bg-ok-tint text-ok">
                <x-icon name="check-circle" class="h-3.5 w-3.5" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-bold tracking-tight text-ok cs-num">{{ $completed }}</p>
            <p class="mt-0.5 text-xs text-meta">Visits finished</p>
        </div>
    </div>
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">No show</p>
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-danger-border bg-danger-tint text-danger">
                <x-icon name="x" class="h-3.5 w-3.5" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-bold tracking-tight text-danger cs-num">{{ $noShow }}</p>
            <p class="mt-0.5 text-xs text-meta">Missed slot</p>
        </div>
    </div>
    <div class="ledger-cell col-span-2 lg:col-span-1">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Walk-ins today</p>
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-hairline bg-surface-subtle text-meta">
                <x-icon name="user" class="h-3.5 w-3.5" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-bold tracking-tight text-bright cs-num">{{ $walkIns }}</p>
            <p class="mt-0.5 text-xs text-meta">Front-desk entries</p>
        </div>
    </div>
</div>

{{-- Unified operational strip: Now calling + Proportional Physician Daily Capacity meters --}}
@if ($currentlyCalled->isNotEmpty() || $physicianCapacity->isNotEmpty())
    <div class="mb-4 grid gap-3 {{ $currentlyCalled->isNotEmpty() && $physicianCapacity->isNotEmpty() ? 'lg:grid-cols-12' : '' }}">
        @if ($currentlyCalled->isNotEmpty())
            <section class="rounded-lg border border-brand-border bg-brand-tint/80 px-4 py-3 {{ $physicianCapacity->isNotEmpty() ? 'lg:col-span-5' : '' }}">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 font-mono text-[11px] font-semibold tracking-wider text-brand uppercase">
                        <span class="h-2 w-2 rounded-full bg-brand" aria-hidden="true"></span>
                        Now calling:
                    </span>
                    @foreach ($currentlyCalled as $called)
                        <span class="inline-flex items-center gap-1.5 rounded-md border border-brand-border bg-surface px-2.5 py-1 text-xs font-medium text-bright">
                            <span class="font-mono font-bold text-brand cs-num">#{{ $called->queue_number }}</span>
                            <span>{{ $called->patient->user->name }}</span>
                            <span class="text-meta">· Dr. {{ $called->physician->user->name }}</span>
                        </span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($physicianCapacity->isNotEmpty())
            <section class="rounded-lg border border-hairline bg-surface px-4 py-3 {{ $currentlyCalled->isNotEmpty() ? 'lg:col-span-7' : '' }}">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <span class="font-mono text-[10px] font-semibold tracking-wider text-meta uppercase">Daily capacity:</span>
                    <span class="font-mono text-[10px] text-meta cs-num">{{ $physicianCapacity->count() }} on schedule</span>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($physicianCapacity as $cap)
                        @php
                            $capLimit = max((int) $cap['limit'], 1);
                            $capPct = min(100, (int) round(($cap['booked'] / $capLimit) * 100));
                            $isFull = $cap['remaining'] === 0;
                        @endphp
                        <div class="min-w-0">
                            <div class="flex items-baseline justify-between gap-2 text-xs">
                                <span class="truncate font-medium text-bright">Dr. {{ $cap['physician']->user->name }}</span>
                                <span class="shrink-0 font-mono text-[11px] font-semibold cs-num {{ $isFull ? 'text-danger' : 'text-brand' }}">
                                    {{ $isFull ? 'Full' : $cap['remaining'].' left' }}
                                    <span class="font-normal text-meta">({{ $cap['booked'] }}/{{ $cap['limit'] }})</span>
                                </span>
                            </div>
                            <div class="capacity-bar mt-1.5" role="progressbar" aria-valuenow="{{ $cap['booked'] }}" aria-valuemin="0" aria-valuemax="{{ $cap['limit'] }}"
                                 aria-label="Dr. {{ $cap['physician']->user->name }} daily capacity">
                                <span class="capacity-fill {{ $isFull ? 'capacity-fill-full' : '' }}" style="width: {{ $capPct }}%"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endif

{{-- Queue ledger container --}}
<div class="card">
    <div class="card-header flex flex-wrap items-center justify-between gap-2">
        <div>
            <h2 class="text-sm font-semibold tracking-tight text-bright">Queue for {{ \Illuminate\Support\Carbon::parse($date)->isoFormat('dddd, MMM D, YYYY') }}</h2>
            <p class="mt-0.5 text-xs text-meta">Live order of consultation for the selected clinic schedule.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($date === now()->toDateString())
                <span class="chip border-brand-border bg-brand-tint text-brand">
                    <span class="h-2 w-2 rounded-full bg-brand" aria-hidden="true"></span>
                    <span>Live updates</span>
                    <span data-poll-status="live-admin-queue" class="font-mono text-[11px] text-meta">· Auto</span>
                </span>
            @endif
            <span class="chip cs-num font-mono text-[11px]">
                <x-icon name="list" class="h-3.5 w-3.5 text-meta" />
                {{ $queue->count() }} appointment{{ $queue->count() === 1 ? '' : 's' }}
            </span>
        </div>
    </div>

    {{-- Mobile stacked view (below md) --}}
    <div class="divide-y divide-hairline md:hidden">
        @forelse ($queue as $appointment)
            @php
                $isCallingRow = $appointment->called_at !== null && $appointment->status === 'confirmed';
            @endphp
            <article class="space-y-3 p-4 {{ $isCallingRow ? 'bg-brand-tint/45' : '' }}"
                     data-queue-row
                     data-status="{{ $appointment->status }}"
                     data-search="#{{ $appointment->queue_number }} {{ $appointment->patient->user->name }} {{ $appointment->patient->patient_number }} {{ $appointment->physician->user->name }}">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="cs-num flex h-7 min-w-8 items-center justify-center rounded-md border border-brand-border bg-brand-tint px-2 font-mono text-xs font-bold text-brand">
                            #{{ $appointment->queue_number }}
                        </span>
                        <span class="badge badge-{{ $isCallingRow ? 'in_progress' : $appointment->status }}">
                            {{ $isCallingRow ? 'Called' : \Illuminate\Support\Str::headline($appointment->status) }}
                        </span>
                        <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                    </div>
                    <span class="cs-num font-mono text-xs font-semibold text-bright">
                        {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }}
                    </span>
                </div>

                <div class="flex items-start gap-3">
                    <x-avatar :name="$appointment->patient->user->name" size="md" />
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-bright">{{ $appointment->patient->user->name }}</p>
                        <p class="font-mono text-xs text-meta">{{ $appointment->patient->patient_number }}</p>
                        <p class="mt-1 text-xs text-read cs-num">
                            Dr. {{ $appointment->physician->user->name }}
                            <span class="text-meta">·</span>
                            {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                        </p>
                        @if ($appointment->called_at)
                            <p class="mt-0.5 font-mono text-[11px] font-medium text-brand cs-num">
                                Called at {{ \Illuminate\Support\Carbon::parse($appointment->called_at)->isoFormat('h:mm A') }}
                            </p>
                        @endif
                        @if ($appointment->encounter)
                            <p class="mt-0.5 font-mono text-[11px] text-meta">
                                Encounter {{ $appointment->encounter->encounter_number }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-hairline pt-2.5">
                    @if (in_array($appointment->status, ['pending', 'confirmed'], true))
                        <form method="POST" action="{{ route('admin.queue.call', $appointment) }}" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="date" value="{{ $date }}">
                            <button type="submit" class="btn btn-primary btn-sm w-full">
                                <x-icon name="arrow-right" class="h-4 w-4" />
                                Call patient
                            </button>
                        </form>
                        @if ($appointment->status === 'confirmed')
                            <form method="POST" action="{{ route('admin.queue.update', $appointment) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <x-icon name="check" class="h-4 w-4" />
                                    Complete
                                </button>
                            </form>
                        @endif
                    @elseif ($appointment->status === 'completed')
                        <span class="inline-flex items-center gap-1 py-1 text-xs font-medium text-ok">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            Completed
                        </span>
                    @else
                        <span class="py-1 text-xs text-meta">—</span>
                    @endif

                    <button type="button"
                            data-drawer-open="queue-drawer-{{ $appointment->id }}"
                            aria-label="Manage queue #{{ $appointment->queue_number }}"
                            class="btn btn-secondary btn-sm">
                        <x-icon name="ellipsis-horizontal" class="h-4 w-4" />
                        Manage
                    </button>
                </div>
            </article>
        @empty
            <div class="p-8">
                <x-empty icon="list" title="No appointments scheduled"
                         description="No online bookings or walk-ins for this date. Adjust the filters or register a walk-in above." />
            </div>
        @endforelse
    </div>

    {{-- Desktop hairline table view (md and up) --}}
    <div class="hidden overflow-x-auto md:block">
        @if ($queue->isNotEmpty())
            <table class="min-w-full">
                <caption class="sr-only">Daily outpatient clinic queue</caption>
                <thead>
                    <tr>
                        <th scope="col" class="table-head">Queue #</th>
                        <th scope="col" class="table-head">Patient</th>
                        <th scope="col" class="table-head">Physician</th>
                        <th scope="col" class="table-head">Time</th>
                        <th scope="col" class="table-head">Source</th>
                        <th scope="col" class="table-head">Status</th>
                        <th scope="col" class="table-head">Called</th>
                        <th scope="col" class="table-head">Encounter</th>
                        <th scope="col" class="table-head text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($queue as $appointment)
                        @php
                            $isCallingRow = $appointment->called_at !== null && $appointment->status === 'confirmed';
                        @endphp
                        <tr class="table-row {{ $isCallingRow ? 'table-row-calling' : '' }}"
                            data-queue-row
                            data-status="{{ $appointment->status }}"
                            data-search="#{{ $appointment->queue_number }} {{ $appointment->patient->user->name }} {{ $appointment->patient->patient_number }} {{ $appointment->physician->user->name }}">
                            <td class="table-cell cs-num">
                                <span class="inline-flex h-7 min-w-9 items-center justify-center rounded-md border border-brand-border bg-brand-tint px-2 font-mono text-xs font-bold text-brand">
                                    #{{ $appointment->queue_number }}
                                </span>
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2.5">
                                    <x-avatar :name="$appointment->patient->user->name" size="sm" />
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-bright">{{ $appointment->patient->user->name }}</p>
                                        <p class="font-mono text-xs text-meta">{{ $appointment->patient->patient_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-cell whitespace-nowrap text-read">Dr. {{ $appointment->physician->user->name }}</td>
                            <td class="table-cell cs-num whitespace-nowrap font-mono text-xs text-bright">
                                {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                            </td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                            </td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $isCallingRow ? 'in_progress' : $appointment->status }}">
                                    {{ $isCallingRow ? 'Called' : \Illuminate\Support\Str::headline($appointment->status) }}
                                </span>
                            </td>
                            <td class="table-cell cs-num whitespace-nowrap font-mono text-xs text-meta">
                                {{ $appointment->called_at ? \Illuminate\Support\Carbon::parse($appointment->called_at)->isoFormat('h:mm A') : '—' }}
                            </td>
                            <td class="table-cell cs-num">
                                @if ($appointment->encounter)
                                    <span class="font-mono text-xs text-read">
                                        {{ $appointment->encounter->encounter_number }}
                                    </span>
                                @else
                                    <span class="text-meta">—</span>
                                @endif
                            </td>
                            <td class="table-cell text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    @if (in_array($appointment->status, ['pending', 'confirmed'], true))
                                        <form method="POST" action="{{ route('admin.queue.call', $appointment) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                                Call
                                            </button>
                                        </form>
                                    @endif
                                    @if ($appointment->status === 'confirmed')
                                        <form method="POST" action="{{ route('admin.queue.update', $appointment) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-success btn-sm" title="Mark completed">
                                                <x-icon name="check" class="h-3.5 w-3.5" />
                                                Complete
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button"
                                            data-drawer-open="queue-drawer-{{ $appointment->id }}"
                                            aria-label="Manage queue #{{ $appointment->queue_number }}"
                                            class="btn btn-secondary btn-sm">
                                        Manage
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-8">
                <x-empty icon="list" title="No appointments scheduled"
                         description="No online bookings or walk-ins for this date. Adjust the filters or register a walk-in above." />
            </div>
        @endif
    </div>
</div>

{{-- Unclipped right-hand slide-over inspector drawers for secondary row management & isolated destructive actions --}}
@foreach ($queue as $appointment)
    <div id="queue-drawer-{{ $appointment->id }}"
         data-drawer
         role="dialog"
         aria-modal="true"
         aria-hidden="true"
         aria-labelledby="queue-drawer-title-{{ $appointment->id }}"
         class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-bright/40 backdrop-blur-xs" data-drawer-close></div>
        <div class="drawer-panel animate-drawer-in">
            <div class="flex items-start justify-between gap-3 border-b border-hairline bg-surface-subtle/80 px-5 py-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="cs-num rounded-md border border-brand-border bg-brand-tint px-2 py-0.5 font-mono text-xs font-bold text-brand">
                            #{{ $appointment->queue_number }}
                        </span>
                        <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                        <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                    </div>
                    <h2 id="queue-drawer-title-{{ $appointment->id }}" class="mt-2 truncate text-base font-bold text-bright">
                        {{ $appointment->patient->user->name }}
                    </h2>
                    <p class="cs-num font-mono text-xs text-meta">
                        {{ $appointment->patient->patient_number }} · Dr. {{ $appointment->physician->user->name }}
                    </p>
                </div>
                <button type="button" data-drawer-close aria-label="Close appointment inspector"
                        class="inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-md text-meta transition hover:bg-rail-hover hover:text-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-brand md:min-h-9 md:min-w-9">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto p-5">
                {{-- Visit summary --}}
                <dl class="grid grid-cols-2 gap-3 rounded-md border border-hairline bg-canvas p-3.5 text-xs">
                    <div>
                        <dt class="section-title">Scheduled slot</dt>
                        <dd class="cs-num mt-1 font-mono font-medium text-bright">
                            {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="section-title">Called at</dt>
                        <dd class="cs-num mt-1 font-mono text-read">
                            {{ $appointment->called_at ? \Illuminate\Support\Carbon::parse($appointment->called_at)->isoFormat('h:mm A') : 'Not yet called' }}
                        </dd>
                    </div>
                    <div class="col-span-2 border-t border-hairline pt-2.5">
                        <dt class="section-title">Chief complaint</dt>
                        <dd class="mt-1 text-sm text-read">{{ $appointment->chief_complaint ?: 'Not specified' }}</dd>
                    </div>
                </dl>

                {{-- Update status --}}
                <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="space-y-2.5">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="date" value="{{ $date }}">
                    <label for="drawer-status-{{ $appointment->id }}" class="section-title block">Update status</label>
                    <div class="flex gap-2">
                        <select id="drawer-status-{{ $appointment->id }}" name="status" class="input flex-1 text-sm">
                            <option value="pending" {{ old('status', $appointment->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ old('status', $appointment->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="completed" {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="no_show" {{ old('status', $appointment->status) === 'no_show' ? 'selected' : '' }}>No show</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>

                {{-- Reschedule --}}
                <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="space-y-3 border-t border-hairline pt-5">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="date" value="{{ $date }}">
                    <p class="section-title">Reschedule appointment</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="drawer-date-{{ $appointment->id }}" class="label text-xs">New date</label>
                            <input id="drawer-date-{{ $appointment->id }}" type="date" name="appointment_date" value="{{ $appointment->appointment_date->toDateString() }}" class="input cs-num text-xs" required>
                        </div>
                        <div>
                            <label for="drawer-time-{{ $appointment->id }}" class="label text-xs">New start time</label>
                            <input id="drawer-time-{{ $appointment->id }}" type="time" name="start_time" value="{{ substr($appointment->start_time, 0, 5) }}" class="input cs-num text-xs" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-secondary w-full">Reschedule visit</button>
                </form>
            </div>

            {{-- Spatially isolated destructive footer zone (outline-only until focused/hovered) --}}
            @if (in_array($appointment->status, ['pending', 'confirmed'], true) || auth()->user()?->hasRole('administrator'))
                <div class="mt-auto space-y-2.5 border-t border-hairline bg-canvas p-5">
                    <p class="section-title">Exceptions &amp; removal</p>
                    @if (in_array($appointment->status, ['pending', 'confirmed'], true))
                        <div class="grid grid-cols-2 gap-2.5">
                            <form method="POST" action="{{ route('admin.queue.update', $appointment) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="status" value="no_show">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-full"
                                        data-confirm="Mark #{{ $appointment->queue_number }} ({{ $appointment->patient->user->name }}) as No Show?">
                                    No show
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.queue.update', $appointment) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-full"
                                        data-confirm="Cancel #{{ $appointment->queue_number }} ({{ $appointment->patient->user->name }})?">
                                    Cancel visit
                                </button>
                            </form>
                        </div>
                    @endif

                    @role('administrator')
                        <form method="POST" action="{{ route('admin.queue.destroy', $appointment) }}" class="pt-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-full"
                                    data-confirm="Remove appointment #{{ $appointment->queue_number }} from the queue? This permanently deletes it.">
                                <x-icon name="trash" class="h-4 w-4" />
                                Remove appointment
                            </button>
                        </form>
                    @endrole
                </div>
            @endif
        </div>
    </div>
@endforeach
