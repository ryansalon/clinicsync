@extends('layouts.app')

@section('title', 'Book an appointment')

@section('content')
    <x-page-header title="Book an appointment" description="Select an available clinic date to reveal open time slots · Mon–Sat, 08:00–17:00." />

    <div class="mx-auto max-w-3xl">
        {{-- Step 1 — availability calendar --}}
        <section class="card">
            <div class="card-header flex flex-wrap items-center justify-between gap-2">
                <x-card-title>Pick a clinic date</x-card-title>
                <span class="chip">
                    <x-icon name="calendar" class="h-3.5 w-3.5 text-brand" />
                    Tap any date to choose a physician &amp; slot
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
        </section>

        {{-- Step 2 — booking form --}}
        <section class="card mt-6 {{ $formVisible ? '' : 'hidden' }}" id="booking-form" tabindex="-1">
            <div class="card-header">
                <x-card-title>Choose your physician and time</x-card-title>
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
                                   class="input cs-num @error('appointment_date') input-invalid @enderror"
                                   min="{{ now()->format('Y-m-d') }}"
                                   max="{{ now()->addDays(\App\Services\PhysicianAvailability::BOOKING_HORIZON_DAYS)->toDateString() }}"
                                   value="{{ old('appointment_date', $selectedDate ?? null) }}" required>
                            @error('appointment_date')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-end">
                            <p class="helper flex items-start gap-1.5">
                                <x-icon name="information-circle" class="mt-0.5 h-4 w-4 shrink-0 text-brand" />
                                <span>Slots reflect the physician's clinic hours in local clinic time.</span>
                            </p>
                        </div>
                    </div>

                    <div>
                        <label for="start_time" class="label">Appointment time</label>
                        <input type="hidden" name="start_time" id="start_time" value="{{ old('start_time') }}">
                        <div id="slot-grid" aria-live="polite" class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                            <p class="col-span-full rounded-md border border-dashed border-hairline-2 bg-surface-subtle/60 px-4 py-8 text-center text-sm text-meta">
                                Select a physician and date to load open slots.
                            </p>
                        </div>
                        <p id="capacity-note" class="mt-2 rounded-md border border-danger-border bg-danger-tint px-3 py-2 text-sm font-medium text-danger" role="alert" hidden></p>
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
                        <label for="reason" class="label">Reason for the visit <span class="font-normal text-meta">(optional)</span></label>
                        <textarea id="reason" name="reason" rows="3" class="input @error('reason') input-invalid @enderror"
                                  placeholder="Anything else the physician should know before your visit…">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="booking-summary" class="rounded-md border border-brand-border bg-brand-tint/65 px-4 py-3 text-xs text-meta" hidden>
                        <p class="font-mono text-[10px] font-semibold tracking-wider text-brand uppercase">Booking summary</p>
                        <p id="booking-summary-text" class="cs-num mt-0.5 text-sm font-medium text-bright"></p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t border-hairline pt-5">
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
        </section>
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

            var IDLE_MESSAGE = 'Select a physician and date to load open slots.';
            var NO_SLOTS_MESSAGE = 'No open slots on this date. Pick another date.';
            var LOADING_MESSAGE = 'Loading available slots…';
            var AVAILABLE_CLASS = 'cs-num font-mono min-h-11 rounded-md border border-hairline-2 bg-surface px-3 py-2 text-xs font-medium text-bright transition-colors hover:border-brand hover:bg-brand-tint hover:text-brand focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand';
            var SELECTED_CLASS = 'cs-num font-mono min-h-11 rounded-md border border-brand bg-brand px-3 py-2 text-xs font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand';
            var DISABLED_CLASS = 'cs-num font-mono min-h-11 rounded-md border border-hairline bg-surface-subtle px-3 py-2 text-xs font-medium text-meta/70 line-through cursor-not-allowed';
            var summaryBox = document.getElementById('booking-summary');
            var summaryText = document.getElementById('booking-summary-text');

            function updateSummary() {
                if (!summaryBox || !summaryText) return;
                var docLabel = physicianSelect.selectedIndex > 0 ? physicianSelect.options[physicianSelect.selectedIndex].text.trim() : '';
                var chosenDate = dateInput.value;
                var selectedBtn = slotContainer.querySelector('button[aria-pressed="true"]');
                var timeLabel = selectedBtn ? selectedBtn.textContent.trim() : '';

                if (docLabel && chosenDate && timeLabel) {
                    summaryText.textContent = docLabel + ' · ' + chosenDate + ' at ' + timeLabel;
                    summaryBox.hidden = false;
                } else {
                    summaryBox.hidden = true;
                }
            }

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
                updateSummary();
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
                    updateSummary();
                    return;
                }

                updateSummary();
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
                        setError('Could not load available slots. Try again.');
                    });
            }

            physicianSelect.addEventListener('change', loadSlots);
            dateInput.addEventListener('change', loadSlots);

            if (bookingForm) {
                bookingForm.addEventListener('submit', function (event) {
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
