@extends('layouts.app')

@section('title', 'My availability')

@php
    $days = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];
@endphp

@section('content')
    <x-page-header title="My availability" description="Post the days you see patients and set how many patients you can cater in a day.">
        <x-slot:actions>
            <button type="button" class="btn btn-primary w-full lg:w-auto" data-dialog-open="availability-dialog">
                <x-icon name="plus" class="h-4 w-4" />
                Add availability
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- Modal dialog prevents header reflow on toggle --}}
    <div id="availability-dialog" data-dialog role="dialog" aria-modal="true" aria-labelledby="availability-dialog-title"
         class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/55 backdrop-blur-xs" data-dialog-close></div>
        <div class="relative max-h-[90dvh] w-full max-w-lg overflow-y-auto rounded-xl border border-[#DCE3E1] bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-[#DCE3E1] px-5 py-4">
                <h2 id="availability-dialog-title" class="text-base font-semibold text-[#14201E]">Add availability window</h2>
                <button type="button" data-dialog-close aria-label="Close availability form"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-[#465854] transition hover:bg-[#EFF4F2] hover:text-[#14201E] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0F766E] md:min-h-9 md:min-w-9">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <form method="POST" action="{{ route('physician.availability.store') }}" class="card-body space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="schedule-day" class="label">Day of week</label>
                        <select id="schedule-day" name="day_of_week" class="input" required>
                            @foreach ($days as $dayValue => $dayLabel)
                                <option value="{{ $dayValue }}" {{ (int) old('day_of_week', 1) === $dayValue ? 'selected' : '' }}>
                                    {{ $dayLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="schedule-start" class="label">Start time</label>
                        <input id="schedule-start" type="time" name="start_time"
                               value="{{ old('start_time') }}"
                               class="input cs-num @error('start_time') input-invalid @enderror" required>
                        @error('start_time')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="schedule-end" class="label">End time</label>
                        <input id="schedule-end" type="time" name="end_time"
                               value="{{ old('end_time') }}"
                               class="input cs-num @error('end_time') input-invalid @enderror" required>
                        @error('end_time')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="schedule-from" class="label">Effective from</label>
                        <input id="schedule-from" type="date" name="effective_from"
                               value="{{ old('effective_from') }}" class="input cs-num">
                    </div>

                    <div>
                        <label for="schedule-until" class="label">Effective until</label>
                        <input id="schedule-until" type="date" name="effective_until"
                               value="{{ old('effective_until') }}"
                               class="input cs-num @error('effective_until') input-invalid @enderror">
                        @error('effective_until')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input id="schedule-active" type="checkbox" name="is_active" value="1"
                           class="h-4 w-4 rounded border-[#C5D0CD] accent-[#0F766E]"
                           {{ old('is_active', 1) ? 'checked' : '' }}>
                    <label for="schedule-active" class="label mb-0">Active schedule</label>
                </div>

                <div class="flex justify-end gap-3 border-t border-[#DCE3E1] pt-4">
                    <button type="button" class="btn btn-secondary" data-dialog-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="plus" class="h-4 w-4" />
                        Save availability
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Daily patient limit --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="card-header">
                <h2 class="text-sm font-semibold tracking-tight text-[#14201E]">Daily patient limit</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('physician.availability.limit') }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    @method('PATCH')

                    <div class="w-44">
                        <label for="daily-patient-limit" class="label">Patients you can cater per day</label>
                        <input id="daily-patient-limit" type="number" name="daily_patient_limit"
                               min="1" max="100" step="1"
                               value="{{ old('daily_patient_limit', $physician->daily_patient_limit) }}"
                               class="input cs-num @error('daily_patient_limit') input-invalid @enderror" required>
                        @error('daily_patient_limit')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" class="h-4 w-4" />
                        Save limit
                    </button>
                </form>

                <p class="mt-4 cs-num text-xs text-[#465854]">
                    {{ $bookedToday }} of {{ $physician->daily_patient_limit }} patient{{ $physician->daily_patient_limit === 1 ? '' : 's' }} booked today.
                    Once the limit is reached, patients see “This date is full” on the availability calendar.
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    @if ($physician->user?->name)
                        <x-avatar :name="$physician->user->name" />
                    @endif
                    <div>
                        <p class="text-sm font-medium text-[#14201E]">Dr. {{ $physician->user?->name ?? '—' }}</p>
                        <p class="text-xs text-[#465854]">{{ $physician->specialization }}</p>
                    </div>
                </div>
                <p class="mt-3 cs-num text-xs text-[#465854]">
                    {{ $schedules->count() }} availability window{{ $schedules->count() === 1 ? '' : 's' }} posted ·
                    {{ $physician->appointment_slot_minutes }}-minute slots
                </p>
            </div>
        </div>
    </div>

    {{-- Own availability windows --}}
    <div class="card mt-6">
        <div class="card-header flex items-center justify-between gap-2">
            <h2 class="text-sm font-semibold tracking-tight text-[#14201E]">Posted availability</h2>
            <span class="chip cs-num">
                <x-icon name="calendar" class="h-3.5 w-3.5 text-[#465854]" />
                {{ $schedules->count() }} window{{ $schedules->count() === 1 ? '' : 's' }}
            </span>
        </div>

        @if ($schedules->isEmpty())
            <div class="card-body">
                <x-empty icon="calendar" title="No availability yet"
                         description="Use “Add availability” to publish the days and hours you see patients." />
            </div>
        @else
            {{-- Mobile stacked cards (below md) --}}
            <div class="divide-y divide-[#DCE3E1] md:hidden">
                @foreach ($schedules as $schedule)
                    <article class="space-y-2 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-semibold text-[#14201E]">
                                {{ $days[$schedule->day_of_week] ?? '—' }}
                            </span>
                            <span class="badge badge-{{ $schedule->is_active ? 'enabled' : 'inactive' }}">
                                {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <p class="cs-num text-xs font-medium text-[#243330]">
                            {{ \Illuminate\Support\Carbon::parse($schedule->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->isoFormat('h:mm A') }}
                        </p>

                        <p class="cs-num text-xs text-[#465854]">
                            @if ($schedule->effective_from && $schedule->effective_until)
                                {{ $schedule->effective_from->isoFormat('MMM D, YYYY') }} – {{ $schedule->effective_until->isoFormat('MMM D, YYYY') }}
                            @elseif ($schedule->effective_from)
                                From {{ $schedule->effective_from->isoFormat('MMM D, YYYY') }}
                            @elseif ($schedule->effective_until)
                                Until {{ $schedule->effective_until->isoFormat('MMM D, YYYY') }}
                            @else
                                Ongoing availability
                            @endif
                        </p>

                        <div class="flex items-center gap-2 border-t border-[#DCE3E1] pt-2">
                            <form method="POST" action="{{ route('physician.availability.update', $schedule) }}" class="flex-1">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="day_of_week" value="{{ $schedule->day_of_week }}">
                                <input type="hidden" name="start_time" value="{{ substr($schedule->start_time, 0, 5) }}">
                                <input type="hidden" name="end_time" value="{{ substr($schedule->end_time, 0, 5) }}">
                                <input type="hidden" name="is_active" value="{{ $schedule->is_active ? 0 : 1 }}">
                                <button type="submit" class="btn btn-secondary btn-sm w-full">
                                    <x-icon name="{{ $schedule->is_active ? 'x' : 'check' }}" class="h-4 w-4" />
                                    {{ $schedule->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('physician.availability.destroy', $schedule) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                        data-confirm="Remove this availability window?">
                                    <x-icon name="trash" class="h-4 w-4" />
                                    Delete
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Desktop table (md and up) --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full">
                    <caption class="sr-only">Posted physician availability schedule windows</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="table-head">Day</th>
                            <th scope="col" class="table-head">Window</th>
                            <th scope="col" class="table-head">Effective</th>
                            <th scope="col" class="table-head">Status</th>
                            <th scope="col" class="table-head text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                            <tr class="table-row">
                                <td class="table-cell font-medium text-[#14201E]">
                                    {{ $days[$schedule->day_of_week] ?? '—' }}
                                </td>
                                <td class="table-cell cs-num whitespace-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($schedule->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->isoFormat('h:mm A') }}
                                </td>
                                <td class="table-cell cs-num whitespace-nowrap">
                                    @if ($schedule->effective_from && $schedule->effective_until)
                                        {{ $schedule->effective_from->isoFormat('MMM D, YYYY') }} – {{ $schedule->effective_until->isoFormat('MMM D, YYYY') }}
                                    @elseif ($schedule->effective_from)
                                        From {{ $schedule->effective_from->isoFormat('MMM D, YYYY') }}
                                    @elseif ($schedule->effective_until)
                                        Until {{ $schedule->effective_until->isoFormat('MMM D, YYYY') }}
                                    @else
                                        Ongoing
                                    @endif
                                </td>
                                <td class="table-cell">
                                    <span class="badge badge-{{ $schedule->is_active ? 'enabled' : 'inactive' }}">
                                        {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="table-cell text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <form method="POST" action="{{ route('physician.availability.update', $schedule) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="day_of_week" value="{{ $schedule->day_of_week }}">
                                            <input type="hidden" name="start_time" value="{{ substr($schedule->start_time, 0, 5) }}">
                                            <input type="hidden" name="end_time" value="{{ substr($schedule->end_time, 0, 5) }}">
                                            <input type="hidden" name="is_active" value="{{ $schedule->is_active ? 0 : 1 }}">
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <x-icon name="{{ $schedule->is_active ? 'x' : 'check' }}" class="h-4 w-4" />
                                                {{ $schedule->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('physician.availability.destroy', $schedule) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                    data-confirm="Remove this availability window?">
                                                <x-icon name="trash" class="h-4 w-4" />
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
