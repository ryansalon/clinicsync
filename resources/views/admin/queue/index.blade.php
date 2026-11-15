@extends('layouts.app')

@section('title', 'Daily queue')

@section('content')
    @php
        $restoredPatientLabel = '';
        if (old('patient_id')) {
            $restoredPatient = \App\Models\Patient::with('user')->find(old('patient_id'));
            if ($restoredPatient) {
                $restoredPatientLabel = ($restoredPatient->user?->name ?? 'Unknown').' ('.$restoredPatient->patient_number.')';
            }
        }

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
        <div class="absolute inset-0 bg-black/55 backdrop-blur-xs" data-dialog-close></div>
        <div class="relative max-h-[90dvh] w-full max-w-2xl overflow-y-auto rounded-xl border border-[#DCE3E1] bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-[#DCE3E1] px-5 py-4">
                <div>
                    <h2 id="walkin-dialog-title" class="text-base font-semibold text-[#14201E]">Register walk-in</h2>
                    <p class="mt-0.5 text-xs text-[#465854]">Clinic hours: Mon–Sat, 08:00–17:00</p>
                </div>
                <button type="button" data-dialog-close aria-label="Close walk-in form"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-[#465854] transition hover:bg-[#EFF4F2] hover:text-[#14201E] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0F766E] md:min-h-9 md:min-w-9">
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
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#465854]">
                            <x-icon name="magnifying-glass" class="h-4 w-4" />
                        </span>
                        <input id="walkin-patient-search" type="search" data-combobox-input
                               value="{{ $restoredPatientLabel }}"
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
                    <p class="helper">Search existing clinic patients by full name or ID (e.g. PT-0001).</p>
                </div>

                <div>
                    <label for="walkin-physician-id" class="label">Physician</label>
                    <select id="walkin-physician-id" name="physician_id"
                            class="input @error('physician_id') input-invalid @enderror" required>
                        <option value="" disabled {{ old('physician_id') ? '' : 'selected' }}>Select a physician</option>
                        @foreach ($physicians as $physician)
                            @php
                                $capMatch = $physicianCapacity->first(fn (array $item) => $item['physician']->id === $physician->id);
                                $remainingNote = $capMatch ? ($capMatch['remaining'] === 0 ? 'Full today' : $capMatch['remaining'].' slots left today') : '';
                            @endphp
                            <option value="{{ $physician->id }}" {{ old('physician_id') == $physician->id ? 'selected' : '' }}>
                                Dr. {{ $physician->user->name }} — {{ $physician->specialization }}{{ $remainingNote ? ' ('.$remainingNote.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('physician_id')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <details class="rounded-lg border border-[#DCE3E1] bg-[#F4F7F6] p-3">
                    <summary class="flex min-h-9 cursor-pointer list-none items-center justify-between text-xs font-semibold text-[#0F766E]">
                        <span>Check monthly physician availability calendar</span>
                        <x-icon name="chevron-right" class="h-4 w-4" />
                    </summary>
                    <div class="mt-3">
                        <x-availability-calendar
                            mode="select"
                            :url="route('admin.availability.calendar')"
                            date-target="walkin-date"
                            select-target="walkin-physician-id" />
                    </div>
                </details>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="walkin-date" class="label">Appointment date</label>
                        <input id="walkin-date" type="date" name="appointment_date"
                               value="{{ old('appointment_date', $date) }}"
                               class="input cs-num @error('appointment_date') input-invalid @enderror" required>
                        @error('appointment_date')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="walkin-time" class="label">Start time</label>
                        <input id="walkin-time" type="time" name="start_time"
                               value="{{ old('start_time', now()->format('H:i')) }}"
                               class="input cs-num @error('start_time') input-invalid @enderror" required>
                        <p class="helper">Mon–Sat · 08:00–17:00</p>
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
                    <textarea id="walkin-reason" name="reason" rows="2"
                              class="input @error('reason') input-invalid @enderror"
                              placeholder="Additional clinical notes">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 border-t border-[#DCE3E1] pt-4">
                    <button type="button" class="btn btn-secondary" data-dialog-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="plus" class="h-4 w-4" />
                        Add to queue
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-filter-bar :action="route('admin.queue.index')" submit-label="Apply" :chips="$filterChips">
        <x-slot:fields>
            <div class="sm:w-48">
                <label for="filter-date" class="label">Date</label>
                <input id="filter-date" type="date" name="date" value="{{ $date }}" class="input cs-num" data-auto-submit>
            </div>

            <div class="flex-1">
                <label for="filter-physician" class="label">Physician</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#465854]">
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
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-[#DCE3E1] bg-white p-2.5 shadow-[0_1px_2px_rgba(20,32,30,0.03)]">
            <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="Filter queue by status">
                <button type="button" data-queue-tab="all" aria-pressed="true" class="btn btn-primary btn-sm">All</button>
                <button type="button" data-queue-tab="waiting" aria-pressed="false" class="btn btn-secondary btn-sm">Waiting</button>
                <button type="button" data-queue-tab="confirmed" aria-pressed="false" class="btn btn-secondary btn-sm">Confirmed</button>
                <button type="button" data-queue-tab="completed" aria-pressed="false" class="btn btn-secondary btn-sm">Completed</button>
                <button type="button" data-queue-tab="no_show" aria-pressed="false" class="btn btn-secondary btn-sm">No show</button>
            </div>
            <div class="relative w-full sm:w-72">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#465854]">
                    <x-icon name="magnifying-glass" class="h-4 w-4" />
                </span>
                <input type="search" data-queue-search class="input pl-9 text-xs"
                       placeholder="Filter by patient, ID or #queue…" aria-label="Filter queue rows">
            </div>
        </div>

        <div id="live-admin-queue"
             data-poll
             data-poll-url="{{ route('admin.queue.index', array_filter(['date' => $date, 'physician_id' => $physicianId])) }}"
             data-poll-interval="15000"
             aria-live="polite">
            @include('admin.queue.partials.board')
        </div>

        <div data-queue-filter-empty class="mt-3 hidden rounded-xl border border-dashed border-[#C5D0CD] bg-white px-4 py-6 text-center text-sm text-[#465854]">
            No queue entries match the active status tab or search filter.
        </div>
    </div>
@endsection
