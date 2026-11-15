{{-- Caseload hairline ledger strip --}}
<div class="ledger-strip lg:grid-cols-4">
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Waiting now</p>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-amber-200 bg-amber-50 text-amber-800">
                <x-icon name="clock" class="h-4 w-4" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-semibold tracking-tight text-amber-900 cs-num">{{ $waiting->count() }}</p>
            <p class="mt-0.5 text-xs text-[#465854]">Ready for consultation</p>
        </div>
    </div>
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Completed today</p>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800">
                <x-icon name="check-circle" class="h-4 w-4" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-semibold tracking-tight text-emerald-800 cs-num">{{ $completedToday }}</p>
            <p class="mt-0.5 text-xs text-[#465854]">Consultations finished</p>
        </div>
    </div>
    <a href="{{ route('physician.encounters.index') }}" class="ledger-cell group hover:bg-[#F4F7F6]">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Encounters recorded</p>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-[#B5DCD4] bg-[#E6F4F1] text-[#0F766E]">
                <x-icon name="clipboard" class="h-4 w-4" />
            </span>
        </div>
        <div class="flex items-end justify-between gap-2">
            <div>
                <p class="text-2xl font-semibold tracking-tight text-[#0F766E] cs-num">{{ $encounterCount }}</p>
                <p class="mt-0.5 text-xs text-[#465854]">Signed chart notes</p>
            </div>
            <span class="text-xs font-medium text-[#0F766E] opacity-0 transition group-hover:opacity-100">Log &rarr;</span>
        </div>
    </a>
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Total in queue today</p>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-[#DCE3E1] bg-[#EFF4F2] text-[#465854]">
                <x-icon name="list" class="h-4 w-4" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-semibold tracking-tight text-[#14201E] cs-num">{{ $queue->count() }}</p>
            <p class="mt-0.5 text-xs text-[#465854]">Scheduled + walk-ins</p>
        </div>
    </div>
</div>

{{-- Next patient up spotlight --}}
@if ($nextPatient)
    <section class="mt-5 rounded-lg border border-[#0F766E]/35 bg-[#E6F4F1] p-5 shadow-[0_1px_3px_rgba(15,118,110,0.08)]">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-4">
                <div class="flex h-14 min-w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-[#0F766E] px-3 text-white">
                    <span class="font-mono text-[10px] font-semibold tracking-wider text-[#E6F4F1] uppercase">Next</span>
                    <span class="font-mono text-lg leading-tight font-bold cs-num">#{{ $nextPatient->queue_number }}</span>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-xs font-semibold tracking-wider text-[#0F766E] uppercase">Next patient up</p>
                        @if ($nextPatient->patient->activeAllergies->isNotEmpty())
                            <span class="inline-flex items-center gap-1 rounded-full border border-rose-300 bg-rose-100 px-2.5 py-0.5 text-[11px] font-semibold text-rose-900">
                                <x-icon name="exclamation-triangle" class="h-3 w-3" />
                                Allergies: {{ $nextPatient->patient->activeAllergies->pluck('allergen')->take(3)->implode(', ') }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-lg font-semibold text-[#14201E]">
                        {{ $nextPatient->patient->user->name }}
                        <span class="font-mono text-xs font-normal text-[#465854]">· {{ $nextPatient->patient->patient_number }}</span>
                    </p>
                    <p class="mt-0.5 text-sm text-[#243330] cs-num">
                        {{ \Illuminate\Support\Carbon::parse($nextPatient->start_time)->isoFormat('h:mm A') }}
                        @if ($nextPatient->chief_complaint)
                            · {{ $nextPatient->chief_complaint }}
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('physician.consultation', $nextPatient) }}" class="btn btn-primary">
                Start consultation
                <x-icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>
    </section>
@endif

{{-- Today's queue table --}}
<div class="mt-5">
    @include('physician.partials.queue-table')
</div>
