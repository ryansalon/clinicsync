@extends('layouts.app')

@section('title', 'Daily queue')

@section('content')
    <x-page-header title="Daily queue" description="Online bookings and walk-in registrations for the selected clinic day.">
        <x-slot:actions>
            <details data-popover class="card w-full shrink-0 overflow-hidden lg:w-auto">
                <summary class="btn btn-primary w-full cursor-pointer list-none lg:w-auto">
                    <x-icon name="plus" class="h-4 w-4" />
                    Register walk-in
                </summary>
                <form method="POST" action="{{ route('admin.queue.store') }}" class="card-body border-t border-slate-200 space-y-4 lg:w-[34rem]">
                    @csrf

                    <div data-combobox="{{ route('admin.queue.patient-search') }}" class="relative">
                        <label for="walkin-patient-search" class="label">Patient</label>
                        <input type="hidden" name="patient_id" id="walkin-patient-id" data-combobox-value
                               value="{{ old('patient_id') }}" required>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <x-icon name="magnifying-glass" class="h-4 w-4" />
                            </span>
                            <input id="walkin-patient-search" type="search" data-combobox-input
                                   class="input pl-9 @error('patient_id') input-invalid @enderror"
                                   placeholder="Search patient name or ID…"
                                   autocomplete="off" role="combobox" aria-expanded="false"
                                   aria-autocomplete="list" aria-controls="walkin-patient-listbox">
                        </div>
                        <ul id="walkin-patient-listbox" data-combobox-listbox role="listbox" hidden
                            class="dropdown-panel left-0 right-0 max-h-60 overflow-y-auto"></ul>
                        <p data-combobox-status aria-live="polite" class="sr-only"></p>
                        @error('patient_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                        <p class="helper">Search by full name or ID (e.g. PT-0001)</p>
                    </div>

                    <div>
                        <label for="walkin-physician-id" class="label">Physician</label>
                        <select id="walkin-physician-id" name="physician_id"
                                class="input @error('physician_id') input-invalid @enderror" required>
                            <option value="" disabled {{ old('physician_id') ? '' : 'selected' }}>Select a physician</option>
                            @foreach ($physicians as $physician)
                                <option value="{{ $physician->id }}" {{ old('physician_id') == $physician->id ? 'selected' : '' }}>
                                    Dr. {{ $physician->user->name }} — {{ $physician->specialization }}
                                </option>
                            @endforeach
                        </select>
                        @error('physician_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                        <p class="label mb-2">Pick a date — see who is available</p>
                        <x-availability-calendar
                            mode="select"
                            :url="route('admin.availability.calendar')"
                            date-target="walkin-date"
                            select-target="walkin-physician-id" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="walkin-date" class="label">Appointment date</label>
                            <input id="walkin-date" type="date" name="appointment_date"
                                   value="{{ old('appointment_date', $date) }}"
                                   class="input @error('appointment_date') input-invalid @enderror" required>
                            @error('appointment_date')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="walkin-time" class="label">Start time</label>
                            <input id="walkin-time" type="time" name="start_time"
                                   value="{{ old('start_time') }}"
                                   class="input @error('start_time') input-invalid @enderror" required>
                            @error('start_time')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="walkin-complaint" class="label">Chief complaint</label>
                        <input id="walkin-complaint" type="text" name="chief_complaint"
                               value="{{ old('chief_complaint') }}"
                               class="input @error('chief_complaint') input-invalid @enderror"
                               placeholder="Primary reason for the visit">
                        @error('chief_complaint')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="walkin-reason" class="label">Reason</label>
                        <textarea id="walkin-reason" name="reason" rows="3"
                                  class="input @error('reason') input-invalid @enderror"
                                  placeholder="Additional notes">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-full sm:w-auto">
                        <x-icon name="plus" class="h-4 w-4" />
                        Add to queue
                    </button>
                </form>
            </details>
        </x-slot:actions>
    </x-page-header>

    {{-- Filter bar --}}
    @php
        $filterChips = [];
        if ($physicianId) {
            $matchedPhysician = $physicians->firstWhere('id', (int) $physicianId);
            if ($matchedPhysician) {
                $filterChips[] = [
                    'label' => 'Dr. '.$matchedPhysician->user->name,
                    'url' => route('admin.queue.index', ['date' => $date]),
                ];
            }
        }
        if ($date !== now()->toDateString()) {
            $filterChips[] = [
                'label' => \Illuminate\Support\Carbon::parse($date)->isoFormat('MMM D, YYYY'),
                'url' => route('admin.queue.index', array_filter(['physician_id' => $physicianId])),
            ];
        }
    @endphp

    <x-filter-bar :action="route('admin.queue.index')" submit-label="Apply" :chips="$filterChips">
        <x-slot:fields>
            <div class="sm:w-52">
                <label for="filter-date" class="label">Date</label>
                <input id="filter-date" type="date" name="date" value="{{ $date }}" class="input" data-auto-submit>
            </div>

            <div class="flex-1">
                <label for="filter-physician" class="label">Physician</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="funnel" class="h-4 w-4" />
                    </span>
                    <select id="filter-physician" name="physician_id" class="input pl-9" data-auto-submit>
                        <option value="">All physicians</option>
                        @foreach ($physicians as $physician)
                            <option value="{{ $physician->id }}" {{ $physicianId == $physician->id ? 'selected' : '' }}>
                                Dr. {{ $physician->user->name }} — {{ $physician->specialization }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-slot:fields>
    </x-filter-bar>

    {{-- Queue summary --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <x-stat label="Waiting" :value="$waiting->count()" icon="clock" tone="amber" />
        <x-stat label="Confirmed" value="{{ $confirmed }}" icon="check-circle" tone="sky" />
        <x-stat label="Completed" value="{{ $completed }}" icon="check" tone="emerald" />
        <x-stat label="No show" value="{{ $noShow }}" icon="exclamation-triangle" tone="rose" />
        <x-stat label="Walk-ins today" value="{{ $walkIns }}" icon="user-plus" tone="teal" />
    </div>

    {{-- Queue container --}}
    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold tracking-tight text-slate-900">Queue for {{ \Illuminate\Support\Carbon::parse($date)->isoFormat('dddd, MMM D, YYYY') }}</h3>
                <p class="mt-0.5 text-xs text-slate-500">Live order of consultation for today's clinic schedule.</p>
            </div>
            <span class="chip">
                <x-icon name="list" class="h-3.5 w-3.5 text-slate-400" />
                {{ $queue->count() }} appointment{{ $queue->count() === 1 ? '' : 's' }}
            </span>
        </div>

        {{-- Mobile card view (below md) --}}
        <div class="divide-y divide-slate-100 md:hidden">
            @forelse ($queue as $appointment)
                <article class="p-4 space-y-3">
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

                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100">
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
                        <tr class="table-row">
                            <td class="table-cell font-semibold text-slate-900">#{{ $appointment->queue_number }}</td>
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
@endsection
