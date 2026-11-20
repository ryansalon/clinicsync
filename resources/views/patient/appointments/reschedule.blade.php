@extends('layouts.app')

@section('title', 'Reschedule appointment')

@section('content')
    <x-page-header title="Reschedule appointment"
                   description="Pick a new clinic date and slot. A new queue number is issued for the updated time." />

    <div class="mx-auto max-w-3xl space-y-6">
        <section class="card">
            <div class="card-body flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <x-avatar :name="$appointment->physician?->user?->name ?? 'Physician'" />
                    <div class="leading-tight">
                        <p class="text-sm font-semibold text-bright">Dr. {{ $appointment->physician?->user?->name ?? '—' }}</p>
                        <p class="cs-num text-xs text-meta">
                            {{ $appointment->physician?->specialization ?? '—' }}
                            · Currently {{ $appointment->appointment_date->isoFormat('MMM D, YYYY') }}
                            at {{ date('g:i A', strtotime($appointment->start_time)) }}
                        </p>
                    </div>
                </div>
                <span class="chip cs-num font-mono text-xs">
                    <x-icon name="clock" class="h-4 w-4 text-meta" />
                    {{ max((int) $appointment->physician?->appointment_slot_minutes, 1) }}-min slot · Queue #{{ $appointment->queue_number }}
                </span>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <x-card-title>New date and time</x-card-title>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('patient.appointments.reschedule.update', $appointment) }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="appointment_date" class="label">New appointment date</label>
                        <input id="appointment_date" type="date" name="appointment_date"
                               class="input cs-num @error('appointment_date') input-invalid @enderror"
                               min="{{ now()->format('Y-m-d') }}"
                               max="{{ now()->addDays(\App\Services\PhysicianAvailability::BOOKING_HORIZON_DAYS)->toDateString() }}"
                               value="{{ old('appointment_date', $appointment->appointment_date->toDateString()) }}" required>
                        @error('appointment_date')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="start_time" class="label">New appointment time</label>
                        <input type="hidden" name="start_time" id="start_time"
                               value="{{ old('start_time', substr($appointment->start_time, 0, 5)) }}">
                        <div id="slot-grid" aria-live="polite" class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                            <p class="col-span-full rounded-md border border-dashed border-hairline-2 bg-surface-subtle/60 px-4 py-8 text-center text-sm text-meta">
                                Loading available slots…
                            </p>
                        </div>
                        <p id="capacity-note" class="mt-2 rounded-md border border-danger-border bg-danger-tint px-3 py-2 text-sm font-medium text-danger" role="alert" hidden></p>
                        <p id="slot-helper" class="helper"></p>
                        <p id="slot-error" class="field-error" role="alert" hidden></p>
                        @error('start_time')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t border-hairline pt-5">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="refresh" class="h-4 w-4" />
                            Confirm new time
                        </button>
                        <a class="btn btn-secondary" href="{{ route('patient.appointments.show', $appointment) }}">
                            <x-icon name="arrow-left" class="h-4 w-4" />
                            Keep current appointment
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            var physicianId = @json($appointment->physician_id);
            var dateInput = document.getElementById('appointment_date');
            var slotContainer = document.getElementById('slot-grid');
            var hiddenStart = document.getElementById('start_time');
            var helper = document.getElementById('slot-helper');
            var errorBox = document.getElementById('slot-error');
            var capacityNote = document.getElementById('capacity-note');
            var appointmentId = @json($appointment->id);
            var rescheduleForm = slotContainer ? slotContainer.closest('form') : null;
            var availabilityUrl = @json(route('patient.appointments.availability'));

            if (!dateInput || !slotContainer || !hiddenStart) {
                return;
            }

            var NO_SLOTS_MESSAGE = 'No open slots on this date. Pick another date.';
            var LOADING_MESSAGE = 'Loading available slots…';
            var AVAILABLE_CLASS = 'cs-num font-mono min-h-11 rounded-md border border-hairline-2 bg-surface px-3 py-2 text-xs font-medium text-bright transition-colors hover:border-brand hover:bg-brand-tint hover:text-brand focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand';
            var SELECTED_CLASS = 'cs-num font-mono min-h-11 rounded-md border border-brand bg-brand px-3 py-2 text-xs font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand';
            var DISABLED_CLASS = 'cs-num font-mono min-h-11 rounded-md border border-hairline bg-surface-subtle px-3 py-2 text-xs font-medium text-meta/70 line-through cursor-not-allowed';

            function setError(message) {
                if (!errorBox) return;
                if (message) {
                    errorBox.textContent = message;
                    errorBox.hidden = false;
                } else {
                    errorBox.textContent = '';
                    errorBox.hidden = true;
                }
            }

            function setCapacityNote(payload) {
                if (!capacityNote) return;

                var full = !!(payload && payload.full);
                var remaining = payload && typeof payload.remaining === 'number' ? payload.remaining : -1;

                if (!full) {
                    capacityNote.hidden = true;
                    capacityNote.textContent = '';
                    return;
                }

                if (remaining === 0) {
                    var who = payload.physician_name ? 'Dr. ' + payload.physician_name : 'This physician';
                    var limit = payload.daily_patient_limit;
                    capacityNote.textContent = 'This date is full — ' + who + ' can cater at most '
                        + limit + ' patient' + (limit === 1 ? '' : 's') + ' a day. Pick another date.';
                } else {
                    capacityNote.textContent = 'No open slots remain on this date. Pick another date.';
                }

                capacityNote.hidden = false;
            }

            function showMessage(message, helperText) {
                slotContainer.innerHTML = '';
                var paragraph = document.createElement('p');
                paragraph.className = 'col-span-full rounded-md border border-dashed border-hairline-2 bg-surface-subtle/60 px-4 py-8 text-center text-sm text-meta';
                paragraph.textContent = message;
                slotContainer.appendChild(paragraph);
                if (helper) helper.textContent = helperText || '';
            }

            function selectSlot(button) {
                hiddenStart.value = button.getAttribute('data-time') || '';
                var buttons = slotContainer.querySelectorAll('button[data-time]');
                Array.prototype.forEach.call(buttons, function (candidate) {
                    candidate.className = candidate.disabled ? DISABLED_CLASS : AVAILABLE_CLASS;
                    candidate.setAttribute('aria-pressed', 'false');
                });
                button.className = SELECTED_CLASS;
                button.setAttribute('aria-pressed', 'true');
                setError('');
            }

            function renderSlots(payload) {
                setCapacityNote(payload);

                var slots = payload && Array.isArray(payload.slots) ? payload.slots : [];
                var minutes = payload && payload.slot_minutes ? payload.slot_minutes : null;
                var helperText = minutes ? 'Consultations are ' + minutes + ' minutes long.' : '';

                if (!slots.length) {
                    showMessage(NO_SLOTS_MESSAGE, helperText);
                    return;
                }

                var grid = document.createElement('div');
                grid.className = 'col-span-full grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4';

                slots.forEach(function (slot) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = slot.label || slot.time;
                    button.setAttribute('data-time', slot.time || '');
                    button.setAttribute('aria-pressed', 'false');

                    if (!slot.available) {
                        button.disabled = true;
                        button.className = DISABLED_CLASS;
                        button.title = 'Unavailable';
                    } else {
                        button.className = AVAILABLE_CLASS;
                        button.addEventListener('click', function () {
                            selectSlot(button);
                        });
                    }

                    grid.appendChild(button);
                });

                slotContainer.innerHTML = '';
                slotContainer.appendChild(grid);

                if (helper) helper.textContent = helperText;

                if (hiddenStart.value) {
                    var chosen = slotContainer.querySelector('button[data-time="' + hiddenStart.value + '"]');
                    if (chosen && !chosen.disabled) {
                        selectSlot(chosen);
                    } else {
                        hiddenStart.value = '';
                        setError('Your previous time is no longer available. Pick a new slot.');
                    }
                }
            }

            function loadSlots() {
                var date = dateInput.value;

                setCapacityNote(null);

                if (!physicianId || !date) {
                    setError('');
                    showMessage('Select a date to load open slots.');
                    return;
                }

                setError('');
                showMessage(LOADING_MESSAGE);

                fetch(availabilityUrl + '?physician_id=' + encodeURIComponent(physicianId)
                    + '&date=' + encodeURIComponent(date)
                    + '&appointment_id=' + encodeURIComponent(appointmentId))
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Availability request failed.');
                        }
                        return response.json();
                    })
                    .then(renderSlots)
                    .catch(function () {
                        showMessage('Select a date to load open slots.');
                        setError('Could not load available slots. Try again.');
                    });
            }

            dateInput.addEventListener('change', loadSlots);

            if (rescheduleForm) {
                rescheduleForm.addEventListener('submit', function (event) {
                    if (!hiddenStart.value) {
                        event.preventDefault();
                        setError('Select an available time slot.');
                        if (slotContainer) {
                            if (window.clinicScrollTo) {
                                window.clinicScrollTo(slotContainer);
                            } else {
                                slotContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }
                    }
                });
            }

            loadSlots();
        })();
    </script>
@endpush
