@extends('layouts.app')

@section('title', 'Book an appointment')

@section('content')
    <x-page-header title="Book an appointment" description="Pick a date to see which physicians are available, then press Book appointment to choose your time slot." />

    <div class="mx-auto max-w-3xl">
        {{-- Step 1 — availability calendar, the first thing the patient sees --}}
        <div class="card">
            <div class="card-header flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-semibold tracking-tight text-slate-900">Step 1 — Pick a date</h3>
                <span class="chip">
                    <x-icon name="calendar" class="h-3.5 w-3.5 text-slate-400" />
                    See who is available
                </span>
            </div>
            <div class="card-body">
                <x-availability-calendar
                    mode="reveal"
                    :url="route('patient.availability.calendar')"
                    date-target="appointment_date"
                    select-target="physician_id"
                    reveal-target="booking-form"
                    :selected-date="$selectedDate" />
            </div>
        </div>

        {{-- Step 2 — the booking form, revealed once a date is chosen --}}
        <div class="card mt-6 {{ $formVisible ? '' : 'hidden' }}" id="booking-form" tabindex="-1">
            <div class="card-header">
                <h3 class="text-sm font-semibold tracking-tight text-slate-900">Step 2 — Choose your physician and time</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('patient.appointments.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="physician_id" class="label">Physician</label>
                        <select id="physician_id" name="physician_id" class="input @error('physician_id') input-invalid @enderror" required>
                            <option value="">Select a physician…</option>
                            @foreach ($physicians as $physician)
                                <option value="{{ $physician->id }}" @selected((string) (old('physician_id') ?: $selectedPhysician?->id) === (string) $physician->id)>
                                    Dr. {{ $physician->user?->name }} — {{ $physician->specialization }}
                                </option>
                            @endforeach
                        </select>
                        @error('physician_id')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="appointment_date" class="label">Appointment date</label>
                            <input id="appointment_date" type="date" name="appointment_date"
                                   class="input @error('appointment_date') input-invalid @enderror"
                                   min="{{ now()->format('Y-m-d') }}"
                                   max="{{ now()->addDays(\App\Services\PhysicianAvailability::BOOKING_HORIZON_DAYS)->toDateString() }}"
                                   value="{{ old('appointment_date', $selectedDate ?? null) }}" required>
                            @error('appointment_date')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-end">
                            <p class="helper flex items-start gap-1.5">
                                <x-icon name="information-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                                <span>Slots are generated from the physician's clinic schedule. Times shown are in the clinic's local time.</span>
                            </p>
                        </div>
                    </div>

                    <div>
                        <label for="start_time" class="label">Appointment time</label>
                        <input type="hidden" name="start_time" id="start_time" value="{{ old('start_time') }}">
                        <div id="slot-grid" aria-live="polite" class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                            <p class="col-span-full rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                Select a physician and date to see available slots.
                            </p>
                        </div>
                        <p id="capacity-note" class="mt-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700" role="alert" hidden></p>
                        <p id="slot-helper" class="helper"></p>
                        <p id="slot-error" class="field-error" role="alert" hidden></p>
                        @error('start_time')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="chief_complaint" class="label">Chief complaint</label>
                        <input id="chief_complaint" type="text" name="chief_complaint"
                               class="input @error('chief_complaint') input-invalid @enderror"
                               value="{{ old('chief_complaint') }}" placeholder="e.g. Fever and sore throat for 3 days" required>
                        @error('chief_complaint')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reason" class="label">Reason for the visit <span class="font-normal text-slate-400">(optional)</span></label>
                        <textarea id="reason" name="reason" rows="4" class="input @error('reason') input-invalid @enderror"
                                  placeholder="Anything else the physician should know before your visit…">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="calendar" class="h-4 w-4" />
                            Book appointment
                        </button>
                        <a class="btn btn-secondary" href="{{ route('patient.appointments.index') }}">
                            <x-icon name="arrow-left" class="h-4 w-4" />
                            Back to my appointments
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

            var physicianSelect = document.getElementById('physician_id');
            var dateInput = document.getElementById('appointment_date');
            var slotContainer = document.getElementById('slot-grid');
            var hiddenStart = document.getElementById('start_time');
            var helper = document.getElementById('slot-helper');
            var errorBox = document.getElementById('slot-error');
            var capacityNote = document.getElementById('capacity-note');
            var bookingForm = slotContainer ? slotContainer.closest('form') : null;
            var availabilityUrl = @json(route('patient.appointments.availability'));

            if (!physicianSelect || !dateInput || !slotContainer || !hiddenStart) {
                return;
            }

            var IDLE_MESSAGE = 'Select a physician and date to see available slots.';
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
            // same wording the server would reject the booking with.
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
                    }
                }
            }

            function loadSlots() {
                var physicianId = physicianSelect.value;
                var date = dateInput.value;

                setCapacityNote(null);

                if (!physicianId || !date) {
                    setError('');
                    showMessage(IDLE_MESSAGE);
                    return;
                }

                setError('');
                showMessage(LOADING_MESSAGE);

                fetch(availabilityUrl + '?physician_id=' + encodeURIComponent(physicianId) + '&date=' + encodeURIComponent(date))
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Availability request failed.');
                        }
                        return response.json();
                    })
                    .then(renderSlots)
                    .catch(function () {
                        showMessage(IDLE_MESSAGE);
                        setError('We could not load available slots. Please try again.');
                    });
            }

            physicianSelect.addEventListener('change', loadSlots);
            dateInput.addEventListener('change', loadSlots);

            if (bookingForm) {
                bookingForm.addEventListener('submit', function (event) {
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

            // Re-populate slots when returning to the form with validation errors.
            loadSlots();
        })();
    </script>
@endpush
