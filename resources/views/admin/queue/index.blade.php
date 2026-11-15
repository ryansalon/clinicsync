@extends('layouts.app')

@section('title', 'Daily queue')

@section('content')
    <x-page-header title="Daily queue" description="Online bookings and walk-in registrations for the selected clinic day.">
        <x-slot:actions>
            @role('administrator')
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <x-icon name="chart-bar" class="h-4 w-4" />
                    System overview
                </a>
            @endrole
            <button type="button" class="btn btn-primary w-full lg:w-auto" data-dialog-open="walkin-dialog">
                <x-icon name="plus" class="h-4 w-4" />
                Register walk-in
            </button>
        </x-slot:actions>
    </x-page-header>

    <div id="walkin-dialog" data-dialog role="dialog" aria-modal="true" aria-labelledby="walkin-dialog-title"
         class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-dialog-close></div>
        <div class="relative max-h-[90dvh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 id="walkin-dialog-title" class="text-base font-semibold text-slate-900">Register walk-in</h2>
                <button type="button" data-dialog-close aria-label="Close"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>
            <form method="POST" action="{{ route('admin.queue.store') }}" class="card-body space-y-4">
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

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                    <button type="button" class="btn btn-secondary" data-dialog-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="plus" class="h-4 w-4" />
                        Add to queue
                    </button>
                </div>
            </form>
        </div>
    </div>

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

    <div data-queue-filter-root>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="Filter queue by status">
                <button type="button" data-queue-tab="all" aria-pressed="true" class="btn btn-primary btn-sm">All</button>
                <button type="button" data-queue-tab="waiting" aria-pressed="false" class="btn btn-secondary btn-sm">Waiting</button>
                <button type="button" data-queue-tab="confirmed" aria-pressed="false" class="btn btn-secondary btn-sm">Confirmed</button>
                <button type="button" data-queue-tab="completed" aria-pressed="false" class="btn btn-secondary btn-sm">Completed</button>
                <button type="button" data-queue-tab="no_show" aria-pressed="false" class="btn btn-secondary btn-sm">No show</button>
            </div>
            <div class="relative w-full sm:w-64">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="magnifying-glass" class="h-4 w-4" />
                </span>
                <input type="search" data-queue-search class="input pl-9 text-xs"
                       placeholder="Filter by patient, ID or #queue…" aria-label="Filter queue rows">
            </div>
        </div>

        <div data-poll
             data-poll-url="{{ route('admin.queue.index', array_filter(['date' => $date, 'physician_id' => $physicianId])) }}"
             data-poll-interval="15000"
             aria-live="polite">
            @include('admin.queue.partials.board')
        </div>
    </div>
@endsection
