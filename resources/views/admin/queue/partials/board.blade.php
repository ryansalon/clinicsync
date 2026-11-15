{{-- Queue summary --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
    <x-stat label="Waiting" :value="$waiting->count()" icon="clock" tone="amber" />
    <x-stat label="Confirmed" value="{{ $confirmed }}" icon="check-circle" tone="sky" />
    <x-stat label="Completed" value="{{ $completed }}" icon="check" tone="emerald" />
    <x-stat label="No show" value="{{ $noShow }}" icon="exclamation-triangle" tone="rose" />
    <x-stat label="Walk-ins today" value="{{ $walkIns }}" icon="user-plus" tone="teal" />
</div>

{{-- Currently called & per-physician slot capacity --}}
@if ($currentlyCalled->isNotEmpty())
    <section class="mb-5 rounded-2xl border border-emerald-200/90 bg-emerald-50/85 px-4 py-3 shadow-xs">
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wider text-emerald-800 uppercase">
                <span class="h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                Now calling:
            </span>
            @foreach ($currentlyCalled as $called)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-white px-2.5 py-0.5 text-xs font-medium text-slate-900">
                    <span class="font-mono font-bold text-emerald-700">#{{ $called->queue_number }}</span>
                    <span>{{ $called->patient->user->name }}</span>
                    <span class="text-slate-400">· Dr. {{ $called->physician->user->name }}</span>
                </span>
            @endforeach
        </div>
    </section>
@endif

@if ($physicianCapacity->isNotEmpty())
    <section class="mb-5 flex flex-wrap items-center gap-2">
        <span class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase mr-1">Daily capacity:</span>
        @foreach ($physicianCapacity as $cap)
            <span class="chip {{ $cap['remaining'] === 0 ? 'border-rose-200 bg-rose-50 text-rose-700' : '' }}">
                <span class="font-medium text-slate-800">Dr. {{ $cap['physician']->user->name }}</span>
                <span class="font-mono text-[11px] {{ $cap['remaining'] === 0 ? 'text-rose-700 font-semibold' : 'text-teal-700 font-semibold' }}">
                    {{ $cap['remaining'] === 0 ? 'Full' : $cap['remaining'].' left' }}
                </span>
                <span class="text-[11px] text-slate-400">({{ $cap['booked'] }}/{{ $cap['limit'] }})</span>
            </span>
        @endforeach
    </section>
@endif

{{-- Queue container --}}
<div class="card">
    <div class="card-header flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-semibold tracking-tight text-slate-900">Queue for {{ \Illuminate\Support\Carbon::parse($date)->isoFormat('dddd, MMM D, YYYY') }}</h3>
            <p class="mt-0.5 text-xs text-slate-500">Live order of consultation for the selected clinic schedule.</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($date === now()->toDateString())
                <span class="chip text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Live updates
                </span>
            @endif
            <span class="chip">
                <x-icon name="list" class="h-3.5 w-3.5 text-slate-400" />
                {{ $queue->count() }} appointment{{ $queue->count() === 1 ? '' : 's' }}
            </span>
        </div>
    </div>

    {{-- Mobile card view (below md) --}}
    <div class="divide-y divide-slate-100 md:hidden">
        @forelse ($queue as $appointment)
            <article class="p-4 space-y-3"
                     data-queue-row
                     data-status="{{ $appointment->status }}"
                     data-search="#{{ $appointment->queue_number }} {{ $appointment->patient->user->name }} {{ $appointment->patient->patient_number }} {{ $appointment->physician->user->name }}">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 min-w-7 items-center justify-center rounded-lg bg-teal-50 px-2 text-xs font-bold text-teal-800">
                            #{{ $appointment->queue_number }}
                        </span>
                        <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                        <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-900">
                        {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }}
                    </span>
                </div>

                <div class="flex items-start gap-3">
                    <x-avatar :name="$appointment->patient->user->name" size="md" />
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-slate-900">{{ $appointment->patient->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $appointment->patient->patient_number }}</p>
                        <p class="mt-1 text-xs text-slate-600">
                            Dr. {{ $appointment->physician->user->name }}
                            <span class="text-slate-400">·</span>
                            {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                        </p>
                        @if ($appointment->called_at)
                            <p class="mt-0.5 text-[11px] font-medium text-emerald-700">
                                Called at {{ \Illuminate\Support\Carbon::parse($appointment->called_at)->isoFormat('h:mm A') }}
                            </p>
                        @endif
                        @if ($appointment->encounter)
                            <p class="mt-0.5 font-mono text-[11px] text-slate-500">
                                Encounter {{ $appointment->encounter->encounter_number }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-slate-100">
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
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 py-1">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            Completed
                        </span>
                    @else
                        <span class="text-xs text-slate-400 py-1">—</span>
                    @endif

                    <div class="relative" data-dropdown>
                        <button type="button" data-dropdown-trigger aria-label="Appointment actions" aria-expanded="false"
                                class="btn btn-secondary btn-sm px-2.5">
                            <x-icon name="ellipsis-horizontal" class="h-4 w-4" />
                            <span>Actions</span>
                        </button>
                        <div data-dropdown-menu class="dropdown-panel right-0 hidden w-72">
                            <div class="p-3 space-y-3">
                                <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <label for="mobile-status-{{ $appointment->id }}" class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase block">Status</label>
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

                                <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="space-y-2 border-t border-slate-100 pt-3">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <p class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Reschedule</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="date" name="appointment_date" value="{{ $appointment->appointment_date->toDateString() }}" class="input py-1 text-xs">
                                        <input type="time" name="start_time" value="{{ substr($appointment->start_time, 0, 5) }}" class="input py-1 text-xs">
                                    </div>
                                    <button type="submit" class="btn btn-secondary btn-sm w-full">Reschedule</button>
                                </form>

                                @role('administrator')
                                    <div class="border-t border-slate-100 pt-2">
                                        <form method="POST" action="{{ route('admin.queue.destroy', $appointment) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="menu-item text-rose-600 hover:bg-rose-50 hover:text-rose-700"
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
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="table-head">Queue #</th>
                    <th class="table-head">Patient</th>
                    <th class="table-head">Physician</th>
                    <th class="table-head">Time</th>
                    <th class="table-head">Source</th>
                    <th class="table-head">Status</th>
                    <th class="table-head">Called</th>
                    <th class="table-head">Encounter</th>
                    <th class="table-head text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($queue as $appointment)
                    <tr class="table-row"
                        data-queue-row
                        data-status="{{ $appointment->status }}"
                        data-search="#{{ $appointment->queue_number }} {{ $appointment->patient->user->name }} {{ $appointment->patient->patient_number }} {{ $appointment->physician->user->name }}">
                        <td class="table-cell">
                            <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-teal-50 px-2.5 font-mono text-xs font-bold text-teal-800 ring-1 ring-teal-200/70">
                                #{{ $appointment->queue_number }}
                            </span>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$appointment->patient->user->name" size="sm" />
                                <div>
                                    <p class="font-medium text-slate-900">{{ $appointment->patient->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $appointment->patient->patient_number }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">Dr. {{ $appointment->physician->user->name }}</td>
                        <td class="table-cell whitespace-nowrap">
                            {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-{{ $appointment->source }}">{{ \Illuminate\Support\Str::headline($appointment->source) }}</span>
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                        </td>
                        <td class="table-cell whitespace-nowrap">
                            {{ $appointment->called_at ? \Illuminate\Support\Carbon::parse($appointment->called_at)->isoFormat('h:mm A') : '—' }}
                        </td>
                        <td class="table-cell">
                            @if ($appointment->encounter)
                                <span class="font-mono text-xs text-slate-600">
                                    {{ $appointment->encounter->encounter_number }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="table-cell text-right">
                            <div class="inline-flex items-center gap-2">
                                @if (in_array($appointment->status, ['pending', 'confirmed'], true))
                                    <form method="POST" action="{{ route('admin.queue.call', $appointment) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="date" value="{{ $date }}">
                                        <button type="submit" class="btn btn-secondary btn-sm">
                                            <x-icon name="arrow-right" class="h-4 w-4" />
                                            Call patient
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
                                            <x-icon name="check" class="h-4 w-4" />
                                            Complete
                                        </button>
                                    </form>
                                @endif

                                <div class="relative" data-dropdown>
                                    <button type="button" data-dropdown-trigger aria-label="Actions for #{{ $appointment->queue_number }}" aria-expanded="false"
                                            class="btn btn-secondary btn-sm p-1.5" title="More actions">
                                        <x-icon name="ellipsis-horizontal" class="h-4 w-4" />
                                    </button>
                                    <div data-dropdown-menu class="dropdown-panel right-0 hidden w-72 text-left">
                                        <div class="p-3 space-y-3">
                                            <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <label for="status-{{ $appointment->id }}" class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase block">Update status</label>
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

                                            <form method="POST" action="{{ route('admin.queue.update', $appointment) }}" class="space-y-2 border-t border-slate-100 pt-3">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <p class="text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Reschedule appointment</p>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="date" name="appointment_date" value="{{ $appointment->appointment_date->toDateString() }}" class="input py-1 text-xs" required>
                                                    <input type="time" name="start_time" value="{{ substr($appointment->start_time, 0, 5) }}" class="input py-1 text-xs" required>
                                                </div>
                                                <button type="submit" class="btn btn-secondary btn-sm w-full">Reschedule</button>
                                            </form>

                                            @role('administrator')
                                                <div class="border-t border-slate-100 pt-2">
                                                    <form method="POST" action="{{ route('admin.queue.destroy', $appointment) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="menu-item text-rose-600 hover:bg-rose-50 hover:text-rose-700"
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
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12">
                            <x-empty icon="list" title="No appointments scheduled"
                                     description="No online bookings or walk-ins for this date. Adjust the filters or register a walk-in above." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
