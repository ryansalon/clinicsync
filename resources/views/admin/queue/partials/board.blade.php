{{-- Compact hairline queue KPI ledger strip (maximizes table rows above fold) --}}
<div class="ledger-strip mb-4 lg:grid-cols-5">
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Waiting</p>
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-amber-200 bg-amber-50 text-amber-800">
                <x-icon name="clock" class="h-3.5 w-3.5" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-semibold tracking-tight text-amber-900 cs-num">{{ $waiting->count() }}</p>
            <p class="mt-0.5 text-xs text-[#465854]">In waiting line</p>
        </div>
    </div>
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Confirmed</p>
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-[#B5DCD4] bg-[#E6F4F1] text-[#0F766E]">
                <x-icon name="check" class="h-3.5 w-3.5" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-semibold tracking-tight text-[#0F766E] cs-num">{{ $confirmed }}</p>
            <p class="mt-0.5 text-xs text-[#465854]">Checked in / ready</p>
        </div>
    </div>
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Completed</p>
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800">
                <x-icon name="check-circle" class="h-3.5 w-3.5" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-semibold tracking-tight text-emerald-800 cs-num">{{ $completed }}</p>
            <p class="mt-0.5 text-xs text-[#465854]">Visits finished</p>
        </div>
    </div>
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">No show</p>
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-rose-200 bg-rose-50 text-rose-800">
                <x-icon name="x" class="h-3.5 w-3.5" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-semibold tracking-tight text-rose-800 cs-num">{{ $noShow }}</p>
            <p class="mt-0.5 text-xs text-[#465854]">Missed slot</p>
        </div>
    </div>
    <div class="ledger-cell col-span-2 lg:col-span-1">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Walk-ins today</p>
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-[#DCE3E1] bg-[#EFF4F2] text-[#465854]">
                <x-icon name="user" class="h-3.5 w-3.5" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-semibold tracking-tight text-[#14201E] cs-num">{{ $walkIns }}</p>
            <p class="mt-0.5 text-xs text-[#465854]">Front-desk entries</p>
        </div>
    </div>
</div>

{{-- Currently called & per-physician daily slot capacity --}}
@if ($currentlyCalled->isNotEmpty())
    <section class="mb-3 rounded-lg border border-[#0F766E]/30 bg-[#E6F4F1] px-4 py-2.5">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wider text-[#0F766E] uppercase">
                <span class="h-2 w-2 rounded-full bg-[#0F766E]" aria-hidden="true"></span>
                Now calling:
            </span>
            @foreach ($currentlyCalled as $called)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-[#B5DCD4] bg-white px-2.5 py-0.5 text-xs font-medium text-[#14201E]">
                    <span class="font-mono font-bold text-[#0F766E] cs-num">#{{ $called->queue_number }}</span>
                    <span>{{ $called->patient->user->name }}</span>
                    <span class="text-[#465854]">· Dr. {{ $called->physician->user->name }}</span>
                </span>
            @endforeach
        </div>
    </section>
@endif

@if ($physicianCapacity->isNotEmpty())
    <section class="mb-4 flex flex-wrap items-center gap-2 rounded-lg border border-[#DCE3E1] bg-[#F4F7F6] px-3.5 py-2.5">
        <span class="mr-1 text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Daily capacity:</span>
        @foreach ($physicianCapacity as $cap)
            <span class="chip items-baseline bg-white {{ $cap['remaining'] === 0 ? 'border-rose-200 bg-rose-50 text-rose-900' : '' }}">
                <span class="text-xs font-medium text-[#14201E]">Dr. {{ $cap['physician']->user->name }}</span>
                <span class="font-mono text-xs font-semibold cs-num {{ $cap['remaining'] === 0 ? 'text-rose-800' : 'text-[#0F766E]' }}">
                    {{ $cap['remaining'] === 0 ? 'Full' : $cap['remaining'].' left' }}
                </span>
                <span class="font-mono text-xs text-[#465854] cs-num">({{ $cap['booked'] }}/{{ $cap['limit'] }})</span>
            </span>
        @endforeach
    </section>
@endif

{{-- Queue container --}}
<div class="card">
    <div class="card-header flex flex-wrap items-center justify-between gap-2">
        <div>
            <h2 class="text-sm font-semibold tracking-tight text-[#14201E]">Queue for {{ \Illuminate\Support\Carbon::parse($date)->isoFormat('dddd, MMM D, YYYY') }}</h2>
            <p class="mt-0.5 text-xs text-[#465854]">Live order of consultation for the selected clinic schedule.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($date === now()->toDateString())
                <span class="chip border-[#B5DCD4] bg-[#E6F4F1] text-[#0F766E]">
                    <span class="h-2 w-2 rounded-full bg-[#0F766E]" aria-hidden="true"></span>
                    <span>Live updates</span>
                    <span data-poll-status="live-admin-queue" class="font-mono text-[11px] text-[#465854]">· Auto</span>
                </span>
            @endif
            <span class="chip cs-num">
                <x-icon name="list" class="h-3.5 w-3.5 text-[#465854]" />
                {{ $queue->count() }} appointment{{ $queue->count() === 1 ? '' : 's' }}
            </span>
        </div>
    </div>

    {{-- Mobile card view (below md) --}}
    <div class="divide-y divide-[#DCE3E1] md:hidden">
        @forelse ($queue as $appointment)
            <article class="space-y-3 p-4"
                     data-queue-row
                     data-status="{{ $appointment->status }}"
                     data-search="#{{ $appointment->queue_number }} {{ $appointment->patient->user->name }} {{ $appointment->patient->patient_number }} {{ $appointment->physician->user->name }}">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="cs-num flex h-7 min-w-7 items-center justify-center rounded-md border border-[#B5DCD4] bg-[#E6F4F1] px-2 font-mono text-xs font-bold text-[#0F766E]">
                            #{{ $appointment->queue_number }}
                        </span>
                        <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                        <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                    </div>
                    <span class="cs-num text-xs font-semibold text-[#14201E]">
                        {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }}
                    </span>
                </div>

                <div class="flex items-start gap-3">
                    <x-avatar :name="$appointment->patient->user->name" size="md" />
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-[#14201E]">{{ $appointment->patient->user->name }}</p>
                        <p class="font-mono text-xs text-[#465854]">{{ $appointment->patient->patient_number }}</p>
                        <p class="mt-1 text-xs text-[#243330] cs-num">
                            Dr. {{ $appointment->physician->user->name }}
                            <span class="text-[#465854]">·</span>
                            {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                        </p>
                        @if ($appointment->called_at)
                            <p class="mt-0.5 text-[11px] font-medium text-[#0F766E] cs-num">
                                Called at {{ \Illuminate\Support\Carbon::parse($appointment->called_at)->isoFormat('h:mm A') }}
                            </p>
                        @endif
                        @if ($appointment->encounter)
                            <p class="mt-0.5 font-mono text-[11px] text-[#465854]">
                                Encounter {{ $appointment->encounter->encounter_number }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[#DCE3E1] pt-2">
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
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <x-icon name="check" class="h-4 w-4" />
                                    Complete
                                </button>
                            </form>
                        @endif
                    @elseif ($appointment->status === 'completed')
                        <span class="inline-flex items-center gap-1 py-1 text-xs font-medium text-[#0F766E]">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            Completed
                        </span>
                    @else
                        <span class="py-1 text-xs text-[#465854]">—</span>
                    @endif

                    <div class="relative" data-dropdown>
                        <button type="button" data-dropdown-trigger aria-label="Appointment actions for #{{ $appointment->queue_number }}" aria-expanded="false"
                                class="btn btn-secondary btn-sm px-2.5">
                            <x-icon name="ellipsis-horizontal" class="h-4 w-4" />
                            <span>Actions</span>
                        </button>
                        <div data-dropdown-menu class="dropdown-panel right-0 hidden w-72">
                            <div class="space-y-3 p-3">
                                @if (in_array($appointment->status, ['pending', 'confirmed'], true))
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <input type="hidden" name="status" value="no_show">
                                            <button type="submit" class="btn btn-secondary btn-sm w-full text-rose-800"
                                                    data-confirm="Mark #{{ $appointment->queue_number }} ({{ $appointment->patient->user->name }}) as No Show?">
                                                No show
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="date" value="{{ $date }}">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-secondary btn-sm w-full"
                                                    data-confirm="Cancel #{{ $appointment->queue_number }} ({{ $appointment->patient->user->name }})?">
                                                Cancel visit
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="space-y-2 border-t border-[#DCE3E1] pt-2.5">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <label for="mobile-status-{{ $appointment->id }}" class="block text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Status</label>
                                    <div class="flex gap-2">
                                        <select id="mobile-status-{{ $appointment->id }}" name="status" class="input py-1 text-xs flex-1">
                                            <option value="pending" {{ old('status', $appointment->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed" {{ old('status', $appointment->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="completed" {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            <option value="no_show" {{ old('status', $appointment->status) === 'no_show' ? 'selected' : '' }}>No show</option>
                                        </select>
                                        <button type="submit" class="btn btn-secondary btn-sm">Update</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="space-y-2 border-t border-[#DCE3E1] pt-2.5">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <p class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Reschedule</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label for="mobile-date-{{ $appointment->id }}" class="sr-only">New date</label>
                                            <input id="mobile-date-{{ $appointment->id }}" type="date" name="appointment_date" value="{{ $appointment->appointment_date->toDateString() }}" class="input cs-num py-1 text-xs" required>
                                        </div>
                                        <div>
                                            <label for="mobile-time-{{ $appointment->id }}" class="sr-only">New start time</label>
                                            <input id="mobile-time-{{ $appointment->id }}" type="time" name="start_time" value="{{ substr($appointment->start_time, 0, 5) }}" class="input cs-num py-1 text-xs" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-secondary btn-sm w-full">Reschedule</button>
                                </form>

                                @role('administrator')
                                    <div class="border-t border-[#DCE3E1] pt-2">
                                        <form method="POST" action="{{ route('admin.queue.destroy', $appointment) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="menu-item text-rose-700 hover:bg-rose-50 hover:text-rose-900"
                                                    data-confirm="Remove appointment #{{ $appointment->queue_number }} from the queue? This permanently deletes it.">
                                                <x-icon name="trash" class="h-4 w-4" />
                                                Remove appointment
                                            </button>
                                        </form>
                                    </div>
                                @endrole
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="p-8">
                <x-empty icon="list" title="No appointments scheduled"
                         description="No online bookings or walk-ins for this date. Adjust the filters or register a walk-in above." />
            </div>
        @endforelse
    </div>

    {{-- Desktop table view (md and up) --}}
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
                    <tr class="table-row"
                        data-queue-row
                        data-status="{{ $appointment->status }}"
                        data-search="#{{ $appointment->queue_number }} {{ $appointment->patient->user->name }} {{ $appointment->patient->patient_number }} {{ $appointment->physician->user->name }}">
                        <td class="table-cell cs-num">
                            <span class="inline-flex h-7 min-w-8 items-center justify-center rounded-md border border-[#B5DCD4] bg-[#E6F4F1] px-2 font-mono text-xs font-bold text-[#0F766E]">
                                #{{ $appointment->queue_number }}
                            </span>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center gap-2.5">
                                <x-avatar :name="$appointment->patient->user->name" size="sm" />
                                <div>
                                    <p class="font-medium text-[#14201E]">{{ $appointment->patient->user->name }}</p>
                                    <p class="font-mono text-xs text-[#465854]">{{ $appointment->patient->patient_number }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">Dr. {{ $appointment->physician->user->name }}</td>
                        <td class="table-cell cs-num whitespace-nowrap">
                            {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                        </td>
                        <td class="table-cell cs-num whitespace-nowrap text-[#465854]">
                            {{ $appointment->called_at ? \Illuminate\Support\Carbon::parse($appointment->called_at)->isoFormat('h:mm A') : '—' }}
                        </td>
                        <td class="table-cell cs-num">
                            @if ($appointment->encounter)
                                <span class="font-mono text-xs text-[#243330]">
                                    {{ $appointment->encounter->encounter_number }}
                                </span>
                            @else
                                <span class="text-[#465854]">—</span>
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

                                <div class="relative" data-dropdown>
                                    <button type="button" data-dropdown-trigger aria-label="Actions for #{{ $appointment->queue_number }}" aria-expanded="false"
                                            class="btn btn-secondary btn-sm px-2" title="More actions">
                                        <x-icon name="ellipsis-horizontal" class="h-4 w-4" />
                                    </button>
                                    <div data-dropdown-menu class="dropdown-panel right-0 hidden w-72 text-left">
                                        <div class="space-y-3 p-3">
                                            @if (in_array($appointment->status, ['pending', 'confirmed'], true))
                                                <div class="flex gap-2">
                                                    <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="flex-1">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="date" value="{{ $date }}">
                                                        <input type="hidden" name="status" value="no_show">
                                                        <button type="submit" class="btn btn-secondary btn-sm w-full text-rose-800"
                                                                data-confirm="Mark #{{ $appointment->queue_number }} ({{ $appointment->patient->user->name }}) as No Show?">
                                                            No show
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="flex-1">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="date" value="{{ $date }}">
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" class="btn btn-secondary btn-sm w-full"
                                                                data-confirm="Cancel #{{ $appointment->queue_number }} ({{ $appointment->patient->user->name }})?">
                                                            Cancel visit
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif

                                            <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="space-y-2 border-t border-[#DCE3E1] pt-2.5">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <label for="status-{{ $appointment->id }}" class="block text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Update status</label>
                                                <div class="flex gap-2">
                                                    <select id="status-{{ $appointment->id }}" name="status" class="input py-1 text-xs flex-1">
                                                        <option value="pending" {{ old('status', $appointment->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="confirmed" {{ old('status', $appointment->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                        <option value="completed" {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                                        <option value="cancelled" {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                        <option value="no_show" {{ old('status', $appointment->status) === 'no_show' ? 'selected' : '' }}>No show</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-secondary btn-sm">Save</button>
                                                </div>
                                            </form>

                                            <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="space-y-2 border-t border-[#DCE3E1] pt-2.5">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <p class="text-[11px] font-semibold tracking-wider text-[#465854] uppercase">Reschedule appointment</p>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <label for="desktop-date-{{ $appointment->id }}" class="sr-only">New appointment date</label>
                                                        <input id="desktop-date-{{ $appointment->id }}" type="date" name="appointment_date" value="{{ $appointment->appointment_date->toDateString() }}" class="input cs-num py-1 text-xs" required>
                                                    </div>
                                                    <div>
                                                        <label for="desktop-time-{{ $appointment->id }}" class="sr-only">New start time</label>
                                                        <input id="desktop-time-{{ $appointment->id }}" type="time" name="start_time" value="{{ substr($appointment->start_time, 0, 5) }}" class="input cs-num py-1 text-xs" required>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-secondary btn-sm w-full">Reschedule</button>
                                            </form>

                                            @role('administrator')
                                                <div class="border-t border-[#DCE3E1] pt-2">
                                                    <form method="POST" action="{{ route('admin.queue.destroy', $appointment) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="menu-item text-rose-700 hover:bg-rose-50 hover:text-rose-900"
                                                                data-confirm="Remove appointment #{{ $appointment->queue_number }} from the queue? This permanently deletes it.">
                                                            <x-icon name="trash" class="h-4 w-4" />
                                                            Remove appointment
                                                        </button>
                                                    </form>
                                                </div>
                                            @endrole
                                        </div>
                                    </div>
                                </div>
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
