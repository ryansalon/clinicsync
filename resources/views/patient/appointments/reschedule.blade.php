@extends('layouts.app')

@section('title', 'Reschedule appointment')

@section('content')
    <x-page-header title="Reschedule appointment"
                   description="Pick a new date and time for this visit. Your queue number will be re-issued for the new slot." />

    <div class="mx-auto max-w-3xl space-y-6">
        {{-- Current appointment summary --}}
        <section class="card">
            <div class="card-body flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <x-avatar :name="$appointment->physician?->user?->name ?? 'Physician'" />
                    <div class="leading-tight">
                        <p class="text-sm font-semibold text-slate-900">Dr. {{ $appointment->physician?->user?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $appointment->physician?->specialization ?? '—' }}
                            · Currently {{ $appointment->appointment_date->isoFormat('MMM D, YYYY') }}
                            at {{ date('g:i A', strtotime($appointment->start_time)) }}
                        </p>
                    </div>
                </div>
                <span class="chip">
                    <x-icon name="clock" class="h-4 w-4 text-slate-400" />
                    {{ max((int) $appointment->physician?->appointment_slot_minutes, 1) }}-minute slot · Queue #{{ $appointment->queue_number }}
                </span>
            </div>
        </section>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('patient.appointments.reschedule.update', $appointment) }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="appointment_date" class="label">New appointment date</label>
                        <input id="appointment_date" type="date" name="appointment_date"
                               class="input @error('appointment_date') input-invalid @enderror"
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
                            <p class="col-span-full rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                Loading available slots…
                            </p>
                        </div>
                        <p id="capacity-note" class="mt-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700" role="alert" hidden></p>
                        <p id="slot-helper" class="helper"></p>
                        <p id="slot-error" class="field-error" role="alert" hidden></p>
                        @error('start_time')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
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
        </div>
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

            var NO_SLOTS_MESSAGE = 'No open slots on this date. Please try another date.';
            var LOADING_MESSAGE = 'Loading available slots…';
            var AVAILABLE_CLASS = 'rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-xs transition hover:border-teal-400 hover:bg-teal-50 hover:text-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500';
            var SELECTED_CLASS = 'rounded-lg border border-teal-600 bg-teal-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500';
            var DISABLED_CLASS = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-400 cursor-not-allowed';

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

            // Warn before submit when the picked date has no room left, using the
            // same wording the server would reject the reschedule with.
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
                        + limit + ' patient' + (limit === 1 ? '' : 's') + ' a day. Please pick another date.';
                } else {
                    capacityNote.textContent = 'No open slots remain on this date. Please pick another date.';
                }

                capacityNote.hidden = false;
            }

            function showMessage(message, helperText) {
                slotContainer.innerHTML = '';
                var paragraph = document.createElement('p');
                paragraph.className = 'col-span-full rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500';
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
                        setError('Your previous time is no longer available. Please pick a new slot.');
                    }
                }
            }

            function loadSlots() {
                var date = dateInput.value;

                setCapacityNote(null);

                if (!physicianId || !date) {
                    setError('');
                    showMessage('Select a date to see available slots.');
                    return;
                }

                setError('');
                showMessage(LOADING_MESSAGE);

                // The appointment itself is excluded, so a full day still shows
                // the slot the patient already holds.
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
                        showMessage('Select a date to see available slots.');
                        setError('We could not load available slots. Please try again.');
                    });
            }

            dateInput.addEventListener('change', loadSlots);

            if (rescheduleForm) {
                rescheduleForm.addEventListener('submit', function (event) {
                    if (!hiddenStart.value) {
                        event.preventDefault();
                        setError('Please select an available time slot.');
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
