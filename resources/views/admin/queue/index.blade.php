@extends('layouts.app')

@section('title', 'Daily queue')

@section('content')
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

    <x-page-header title="Daily queue" description="Online bookings and walk-in triage for the selected clinic day.">
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

    {{-- Walk-in registration modal dialog --}}
    <div id="walkin-dialog" data-dialog role="dialog" aria-modal="true" aria-labelledby="walkin-dialog-title"
         class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-bright/45 backdrop-blur-xs" data-dialog-close></div>
        <div class="relative max-h-[90dvh] w-full max-w-2xl overflow-y-auto rounded-lg border border-hairline bg-surface shadow-[0_16px_40px_-10px_rgba(24,32,29,0.22)]">
            <div class="flex items-center justify-between border-b border-hairline bg-surface-subtle/80 px-5 py-4">
                <div>
                    <h2 id="walkin-dialog-title" class="text-base font-semibold text-bright">Register walk-in</h2>
                    <p class="mt-0.5 font-mono text-xs text-meta">Clinic hours: Mon–Sat · 08:00–17:00</p>
                </div>
                <button type="button" data-dialog-close aria-label="Close walk-in form"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md text-meta transition hover:bg-rail-hover hover:text-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-brand md:min-h-9 md:min-w-9">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>
            <form method="POST" action="{{ route('admin.queue.store') }}" class="card-body space-y-4">
                @csrf

                {{-- Walk-in patient details (Patient ID auto-generated on submit) --}}
                <div class="space-y-4 rounded-md border border-hairline bg-canvas/70 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-hairline pb-2.5 text-xs">
                        <span class="section-title">Walk-in patient details</span>
                        <span class="chip border-brand-border bg-brand-tint font-mono text-[11px] text-brand cs-num">
                            ID auto-generated · {{ $nextPatientNumber }}
                        </span>
                    </div>

                    <div>
                        <label for="walkin-patient-name" class="label">Full name <span class="text-danger">*</span></label>
                        <input id="walkin-patient-name" type="text" name="patient_name"
                               value="{{ old('patient_name') }}"
                               class="input @error('patient_name') input-invalid @enderror"
                               placeholder="e.g. Juan Dela Cruz"
                               autocomplete="name" required>
                        @error('patient_name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="walkin-dob" class="label">Date of birth <span class="text-danger">*</span></label>
                            <input id="walkin-dob" type="date" name="date_of_birth"
                                   value="{{ old('date_of_birth') }}"
                                   max="{{ now()->toDateString() }}"
                                   class="input cs-num @error('date_of_birth') input-invalid @enderror" required>
                            @error('date_of_birth')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="walkin-sex" class="label">Sex <span class="text-danger">*</span></label>
                            <select id="walkin-sex" name="sex"
                                    class="input @error('sex') input-invalid @enderror" required>
                                <option value="">Select…</option>
                                @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $sexValue => $sexLabel)
                                    <option value="{{ $sexValue }}" @selected(old('sex') === $sexValue)>{{ $sexLabel }}</option>
                                @endforeach
                            </select>
                            @error('sex')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="walkin-phone" class="label">Mobile number <span class="font-normal text-meta">(optional)</span></label>
                            <input id="walkin-phone" type="tel" name="phone"
                                   value="{{ old('phone') }}"
                                   inputmode="numeric" autocomplete="tel"
                                   class="input cs-num @error('phone') input-invalid @enderror"
                                   placeholder="09XXXXXXXXX">
                            @error('phone')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="walkin-email" class="label">Email <span class="font-normal text-meta">(optional)</span></label>
                            <input id="walkin-email" type="email" name="email"
                                   value="{{ old('email') }}"
                                   autocomplete="email"
                                   class="input @error('email') input-invalid @enderror"
                                   placeholder="Auto-assigned if blank">
                            @error('email')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
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

                <details class="rounded-md border border-hairline bg-surface-subtle/75 p-3">
                    <summary class="flex min-h-9 cursor-pointer list-none items-center justify-between text-xs font-semibold text-brand">
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

                <div class="flex justify-end gap-3 border-t border-hairline pt-4">
                    <button type="button" class="btn btn-secondary" data-dialog-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="plus" class="h-4 w-4" />
                        Add to queue
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div data-queue-filter-root>
        {{-- Consolidated single-bar command toolbar (Date, Physician, Status tabs & Quick Search) --}}
        <div class="card mb-4">
            <div class="flex flex-col gap-3 p-3.5 xl:flex-row xl:items-center xl:justify-between">
                <form method="GET" action="{{ route('admin.queue.index') }}" class="flex flex-wrap items-center gap-2.5">
                    <div class="w-40">
                        <label for="filter-date" class="sr-only">Clinic date</label>
                        <input id="filter-date" type="date" name="date" value="{{ $date }}" class="input cs-num min-h-9 py-1.5 text-xs" data-auto-submit>
                    </div>
                    <div class="min-w-48 flex-1 sm:flex-none sm:w-60">
                        <label for="filter-physician" class="sr-only">Physician</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-meta">
                                <x-icon name="funnel" class="h-3.5 w-3.5" />
                            </span>
                            <select id="filter-physician" name="physician_id" class="input min-h-9 py-1.5 pl-8 text-xs" data-auto-submit>
                                <option value="">All physicians</option>
                                @foreach ($physicians as $physician)
                                    <option value="{{ $physician->id }}" {{ $physicianId == $physician->id ? 'selected' : '' }}>
                                        Dr. {{ $physician->user->name }} — {{ $physician->specialization }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
                    @if ($date !== now()->toDateString())
                        <a href="{{ route('admin.queue.index', array_filter(['physician_id' => $physicianId])) }}"
                           class="btn btn-ghost btn-sm text-brand">
                            Today
                        </a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-2.5">
                    <div class="tab-strip" role="group" aria-label="Filter queue by status">
                        <button type="button" data-queue-tab="all" aria-pressed="true" class="tab-btn tab-btn-active">All</button>
                        <button type="button" data-queue-tab="waiting" aria-pressed="false" class="tab-btn">Waiting</button>
                        <button type="button" data-queue-tab="confirmed" aria-pressed="false" class="tab-btn">Confirmed</button>
                        <button type="button" data-queue-tab="completed" aria-pressed="false" class="tab-btn">Completed</button>
                        <button type="button" data-queue-tab="no_show" aria-pressed="false" class="tab-btn">No show</button>
                    </div>
                    <div class="relative w-full sm:w-64">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-meta">
                            <x-icon name="magnifying-glass" class="h-3.5 w-3.5" />
                        </span>
                        <input type="search" data-queue-search class="input min-h-9 py-1.5 pr-9 pl-8 text-xs"
                               placeholder="Patient, PT-ID, or #queue…" aria-label="Filter queue rows">
                        <kbd aria-hidden="true"
                             class="pointer-events-none absolute inset-y-0 right-2 my-auto hidden h-5 items-center rounded border border-hairline bg-surface-subtle px-1.5 font-mono text-[10px] font-semibold text-meta sm:inline-flex">
                            /
                        </kbd>
                    </div>
                </div>
            </div>

            @if (count($filterChips) > 0)
                <div class="flex flex-wrap items-center gap-2 border-t border-hairline bg-surface-subtle/60 px-3.5 py-2">
                    <span class="font-mono text-[10px] font-semibold tracking-wider text-meta uppercase">Active</span>
                    @foreach ($filterChips as $chip)
                        <a href="{{ $chip['url'] }}"
                           class="chip min-h-8 bg-surface transition hover:border-brand hover:bg-brand-tint hover:text-brand"
                           aria-label="Remove filter {{ $chip['label'] }}">
                            {{ $chip['label'] }}
                            <x-icon name="x" class="h-3.5 w-3.5" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div id="live-admin-queue"
             data-poll
             data-poll-url="{{ route('admin.queue.index', array_filter(['date' => $date, 'physician_id' => $physicianId])) }}"
             data-poll-interval="15000"
             aria-live="polite">
            @include('admin.queue.partials.board')
        </div>

        <div data-queue-filter-empty class="mt-3 hidden rounded-lg border border-dashed border-hairline-2 bg-surface px-4 py-6 text-center text-sm text-meta">
            No queue entries match the active status tab or search filter.
        </div>
    </div>
@endsection
