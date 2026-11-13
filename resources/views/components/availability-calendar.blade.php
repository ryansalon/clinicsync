@props([
    'url',
    'mode' => 'link',
    'dateTarget' => null,
    'selectTarget' => null,
    'revealTarget' => null,
    'bookUrl' => null,
    'selectedDate' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}
     data-availability-calendar
     data-url="{{ $url }}"
     data-mode="{{ $mode }}"
     @if ($dateTarget) data-date-target="{{ $dateTarget }}" @endif
     @if ($selectTarget) data-select-target="{{ $selectTarget }}" @endif
     @if ($revealTarget) data-reveal-target="{{ $revealTarget }}" @endif
     @if ($bookUrl) data-book-url="{{ $bookUrl }}" @endif
     @if ($selectedDate) data-selected-date="{{ $selectedDate }}" @endif
     aria-label="Physician availability calendar">
    <div class="flex items-center justify-between gap-2">
        <button type="button" data-cal-prev disabled class="btn btn-secondary btn-sm" aria-label="Previous month">
            <x-icon name="chevron-left" class="h-4 w-4" />
        </button>
        <p data-cal-label class="text-sm font-semibold text-slate-900" aria-live="polite">&nbsp;</p>
        <button type="button" data-cal-next disabled class="btn btn-secondary btn-sm" aria-label="Next month">
            <x-icon name="chevron-right" class="h-4 w-4" />
        </button>
    </div>

    <div class="grid grid-cols-7 gap-1">
        @foreach (['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $weekdayLabel)
            <span class="text-center text-[11px] font-semibold tracking-wider text-slate-400 uppercase">{{ $weekdayLabel }}</span>
        @endforeach
    </div>

    <div data-cal-grid class="grid grid-cols-7 gap-1"></div>

    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-500">
        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-teal-500"></span> Available</span>
        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Full</span>
        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span> No availability</span>
    </div>

    <div data-cal-panel class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
        Loading physician availability…
    </div>
</div>

@pushOnce('scripts')
    <script>
        (function () {
            'use strict';

            var MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            var WEEKDAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            var DAY_BASE = 'flex h-9 w-full items-center justify-center rounded-lg text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-teal-500 max-md:min-h-11';
            var DAY_PAST = 'bg-slate-50 text-slate-300 cursor-not-allowed';
            var DAY_AVAILABLE = 'bg-teal-50 text-teal-700 hover:bg-teal-100';
            var DAY_FULL = 'bg-rose-50 text-rose-600 hover:bg-rose-100';
            var DAY_NONE = 'bg-slate-50 text-slate-400 hover:bg-slate-100';
            var DAY_SELECTED = 'bg-teal-600 text-white shadow-sm';
            var DAY_TODAY = 'border border-teal-500';
            var PANEL_IDLE = 'rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center';
            var PANEL_LIST = 'rounded-lg border border-slate-200 bg-slate-50 p-3';

            var roots = document.querySelectorAll('[data-availability-calendar]');

            for (var i = 0; i < roots.length; i++) {
                initCalendar(roots[i]);
            }

            function initCalendar(root) {
                var url = root.getAttribute('data-url');
                var mode = root.getAttribute('data-mode') || 'link';
                var dateTargetId = root.getAttribute('data-date-target');
                var selectTargetId = root.getAttribute('data-select-target');
                var revealTargetId = root.getAttribute('data-reveal-target');
                var bookUrl = root.getAttribute('data-book-url') || '';
                var dateInput = dateTargetId ? document.getElementById(dateTargetId) : null;
                var selectInput = selectTargetId ? document.getElementById(selectTargetId) : null;
                var revealEl = revealTargetId ? document.getElementById(revealTargetId) : null;

                var grid = root.querySelector('[data-cal-grid]');
                var label = root.querySelector('[data-cal-label]');
                var panel = root.querySelector('[data-cal-panel]');
                var prevBtn = root.querySelector('[data-cal-prev]');
                var nextBtn = root.querySelector('[data-cal-next]');

                if (!grid || !label || !panel || !prevBtn || !nextBtn) {
                    return;
                }

                var payload = null;
                var physicianById = {};
                var selected = root.getAttribute('data-selected-date') || (dateInput && dateInput.value) || '';
                var chosenPhysician = selectInput && selectInput.value ? parseInt(selectInput.value, 10) : null;
                var cursor = monthOf(selected || todayLocal());

                prevBtn.addEventListener('click', function () {
                    cursor = cursor.m === 1 ? { y: cursor.y - 1, m: 12 } : { y: cursor.y, m: cursor.m - 1 };
                    load();
                });

                nextBtn.addEventListener('click', function () {
                    cursor = cursor.m === 12 ? { y: cursor.y + 1, m: 1 } : { y: cursor.y, m: cursor.m + 1 };
                    load();
                });

                load();

                function pad(value) {
                    return (value < 10 ? '0' : '') + value;
                }

                function fmt(year, month, day) {
                    return year + '-' + pad(month) + '-' + pad(day);
                }

                function todayLocal() {
                    var now = new Date();
                    return fmt(now.getFullYear(), now.getMonth() + 1, now.getDate());
                }

                function monthOf(dateString) {
                    var parts = dateString.split('-');
                    return { y: parseInt(parts[0], 10), m: parseInt(parts[1], 10) };
                }

                function monthKey() {
                    return cursor.y + '-' + pad(cursor.m);
                }

                function daysInMonth(year, month) {
                    return new Date(year, month, 0).getDate();
                }

                function load() {
                    prevBtn.disabled = true;
                    nextBtn.disabled = true;

                    fetch(url + '?month=' + monthKey(), { headers: { 'Accept': 'application/json' } })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Availability request failed.');
                            }
                            return response.json();
                        })
                        .then(function (data) {
                            payload = data;
                            physicianById = {};

                            (payload.physicians || []).forEach(function (doctor) {
                                physicianById[doctor.id] = doctor;
                            });

                            if (!selected && payload.dates && payload.dates[payload.today]) {
                                selected = payload.today;
                            }

                            if (dateInput && !dateInput.value && selected) {
                                dateInput.value = selected;
                            }

                            render();
                            renderPanel();
                        })
                        .catch(function () {
                            setPanel('We could not load physician availability. Please try again.');
                        });
                }

                function stateOf(dateString) {
                    if (!payload || !payload.dates || !payload.dates[dateString]) {
                        return 'none';
                    }
                    return payload.dates[dateString].state;
                }

                function render() {
                    label.textContent = MONTH_NAMES[cursor.m - 1] + ' ' + cursor.y;

                    var todayMonth = monthOf(payload.today);
                    var horizonMonth = monthOf(payload.horizon);
                    prevBtn.disabled = cursor.y < todayMonth.y || (cursor.y === todayMonth.y && cursor.m <= todayMonth.m);
                    nextBtn.disabled = cursor.y > horizonMonth.y || (cursor.y === horizonMonth.y && cursor.m >= horizonMonth.m);

                    grid.innerHTML = '';

                    var firstWeekday = new Date(cursor.y, cursor.m - 1, 1).getDay();
                    var totalDays = daysInMonth(cursor.y, cursor.m);

                    for (var spacerIndex = 0; spacerIndex < firstWeekday; spacerIndex++) {
                        var spacer = document.createElement('span');
                        spacer.setAttribute('aria-hidden', 'true');
                        grid.appendChild(spacer);
                    }

                    for (var day = 1; day <= totalDays; day++) {
                        grid.appendChild(buildDay(day));
                    }
                }

                function buildDay(dayNumber) {
                    var dateString = fmt(cursor.y, cursor.m, dayNumber);
                    var state = stateOf(dateString);
                    var button = document.createElement('button');
                    var isSelected = dateString === selected;
                    var classes = DAY_BASE;

                    button.type = 'button';
                    button.setAttribute('data-date', dateString);

                    if (state === 'past' || state === 'closed') {
                        button.disabled = true;
                        classes += ' ' + DAY_PAST;
                    } else if (isSelected) {
                        classes += ' ' + DAY_SELECTED;
                    } else if (state === 'available') {
                        classes += ' ' + DAY_AVAILABLE;
                    } else if (state === 'full') {
                        classes += ' ' + DAY_FULL;
                    } else {
                        classes += ' ' + DAY_NONE;
                    }

                    if (dateString === payload.today && !isSelected) {
                        classes += ' ' + DAY_TODAY;
                    }

                    button.className = classes;
                    button.textContent = dayNumber;
                    button.setAttribute('aria-label', ariaLabel(dateString, state));
                    button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');

                    if (!button.disabled) {
                        button.addEventListener('click', function () {
                            selectDate(dateString);
                        });
                    }

                    return button;
                }

                function ariaLabel(dateString, state) {
                    var parts = dateString.split('-');
                    var dateObject = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                    var readable = WEEKDAY_NAMES[dateObject.getDay()] + ', ' + MONTH_NAMES[dateObject.getMonth()] + ' ' + dateObject.getDate() + ', ' + dateObject.getFullYear();
                    var stateLabel = state === 'available' ? 'available' : (state === 'full' ? 'full' : (state === 'none' ? 'no availability' : 'unavailable'));

                    return readable + ' — ' + stateLabel;
                }

                function selectDate(dateString) {
                    selected = dateString;

                    if (dateInput && dateInput.value !== dateString) {
                        dateInput.value = dateString;
                        dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    render();
                    renderPanel();
                }

                function choosePhysician(physicianId) {
                    chosenPhysician = physicianId;

                    if (selectInput && selectInput.value !== String(physicianId)) {
                        selectInput.value = String(physicianId);
                        selectInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    renderPanel();
                }

                function setPanel(message) {
                    panel.className = PANEL_IDLE;
                    panel.innerHTML = '';

                    var paragraph = document.createElement('p');
                    paragraph.className = 'text-sm text-slate-500';
                    paragraph.textContent = message;
                    panel.appendChild(paragraph);
                }

                function renderPanel() {
                    var entry = selected && payload && payload.dates ? payload.dates[selected] : null;

                    if (!entry || entry.state === 'past' || entry.state === 'closed') {
                        setPanel('Pick a date to see which physicians are available.');
                        return;
                    }

                    panel.className = PANEL_LIST;
                    panel.innerHTML = '';

                    panel.appendChild(buildDateTitle());

                    if (entry.state === 'none') {
                        var empty = document.createElement('p');
                        empty.className = 'mt-1 text-xs text-slate-500';
                        empty.textContent = 'No physician availability is posted for this date. Please pick another date.';
                        panel.appendChild(empty);

                        return;
                    }

                    if (entry.state === 'full') {
                        var full = document.createElement('p');
                        full.className = 'mt-1 text-xs font-medium text-rose-600';
                        full.textContent = 'This date is full — every physician is already fully booked.';
                        panel.appendChild(full);
                    }

                    var list = document.createElement('div');
                    list.className = 'mt-3 space-y-2';

                    entry.physicians.forEach(function (info) {
                        var doctor = physicianById[info.id];

                        if (doctor) {
                            list.appendChild(buildPhysicianRow(doctor, info));
                        }
                    });

                    panel.appendChild(list);

                    if (mode === 'reveal' && entry.state === 'available') {
                        panel.appendChild(buildBookFooter());
                    }
                }

                function buildBookFooter() {
                    var footer = document.createElement('div');
                    footer.className = 'mt-3 border-t border-slate-200 pt-3';

                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-primary w-full';
                    button.textContent = 'Book appointment';
                    button.addEventListener('click', revealBooking);

                    footer.appendChild(button);

                    return footer;
                }

                function revealBooking() {
                    if (!revealEl) {
                        return;
                    }

                    // Exactly one physician open on this date and none chosen yet → preselect them.
                    if (selectInput && !selectInput.value && selected && payload && payload.dates && payload.dates[selected]) {
                        var openPhysicians = payload.dates[selected].physicians.filter(function (info) {
                            return info.state === 'available';
                        });

                        if (openPhysicians.length === 1) {
                            selectInput.value = String(openPhysicians[0].id);
                            chosenPhysician = openPhysicians[0].id;
                        }
                    }

                    if (dateInput) {
                        dateInput.value = selected;
                        dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    revealEl.classList.remove('hidden');
                    revealEl.focus({ preventScroll: true });

                    if (window.clinicScrollTo) {
                        window.clinicScrollTo(revealEl, 'start');
                    } else {
                        revealEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }

                    renderPanel();
                }

                function buildDateTitle() {
                    var parts = selected.split('-');
                    var dateObject = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                    var title = document.createElement('p');

                    title.className = 'text-sm font-semibold text-slate-900';
                    title.textContent = WEEKDAY_NAMES[dateObject.getDay()] + ', ' + MONTH_NAMES[dateObject.getMonth()] + ' ' + dateObject.getDate() + ', ' + dateObject.getFullYear();

                    return title;
                }

                function buildPhysicianRow(doctor, info) {
                    var row = document.createElement('div');
                    row.className = 'flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5'
                        + (chosenPhysician === doctor.id ? ' ring-2 ring-teal-500' : '');

                    var details = document.createElement('div');
                    details.className = 'min-w-0';

                    var name = document.createElement('p');
                    name.className = 'text-sm font-medium text-slate-900';
                    name.textContent = 'Dr. ' + doctor.name;

                    var specialization = document.createElement('p');
                    specialization.className = 'text-xs text-slate-500';
                    specialization.textContent = doctor.specialization;

                    details.appendChild(name);
                    details.appendChild(specialization);

                    if (info.state === 'available') {
                        var slotsLeft = document.createElement('p');
                        slotsLeft.className = 'text-xs font-medium text-teal-700';
                        slotsLeft.textContent = info.remaining + ' slot' + (info.remaining === 1 ? '' : 's') + ' left on this date';
                        details.appendChild(slotsLeft);
                    }

                    var side = document.createElement('div');
                    side.className = 'flex shrink-0 items-center gap-2';

                    if (info.state === 'available') {
                        var availableBadge = document.createElement('span');
                        availableBadge.className = 'badge badge-enabled';
                        availableBadge.textContent = 'Available';
                        side.appendChild(availableBadge);
                        side.appendChild(buildAction(doctor));
                    } else if (info.state === 'full') {
                        var fullBadge = document.createElement('span');
                        fullBadge.className = 'badge badge-suspended';
                        fullBadge.textContent = 'This date is full';
                        side.appendChild(fullBadge);
                    } else {
                        var notAvailable = document.createElement('span');
                        notAvailable.className = 'text-xs text-slate-400';
                        notAvailable.textContent = 'Not available this day';
                        side.appendChild(notAvailable);
                    }

                    row.appendChild(details);
                    row.appendChild(side);

                    return row;
                }

                function buildAction(doctor) {
                    if (mode === 'select' || mode === 'reveal') {
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'btn btn-primary btn-sm';
                        button.textContent = 'Select';
                        button.addEventListener('click', function () {
                            choosePhysician(doctor.id);
                        });

                        return button;
                    }

                    var link = document.createElement('a');
                    link.className = 'btn btn-primary btn-sm';
                    link.href = bookUrl + '?physician_id=' + doctor.id + '&date=' + selected;
                    link.textContent = 'Book';

                    return link;
                }
            }
        })();
    </script>
@endpushOnce
