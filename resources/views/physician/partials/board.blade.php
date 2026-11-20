{{-- Caseload hairline ledger strip --}}
<div class="ledger-strip lg:grid-cols-4">
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Waiting now</p>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-warn-border bg-warn-tint text-warn">
                <x-icon name="clock" class="h-4 w-4" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-bold tracking-tight text-warn cs-num">{{ $waiting->count() }}</p>
            <p class="mt-0.5 text-xs text-meta">Ready for consultation</p>
        </div>
    </div>
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Completed today</p>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-ok-border bg-ok-tint text-ok">
                <x-icon name="check-circle" class="h-4 w-4" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-bold tracking-tight text-ok cs-num">{{ $completedToday }}</p>
            <p class="mt-0.5 text-xs text-meta">Consultations finished</p>
        </div>
    </div>
    <a href="{{ route('physician.encounters.index') }}" class="ledger-cell group hover:bg-canvas">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Encounters recorded</p>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-brand-border bg-brand-tint text-brand">
                <x-icon name="clipboard" class="h-4 w-4" />
            </span>
        </div>
        <div class="flex items-end justify-between gap-2">
            <div>
                <p class="text-2xl font-bold tracking-tight text-brand cs-num">{{ $encounterCount }}</p>
                <p class="mt-0.5 text-xs text-meta">Signed chart notes</p>
            </div>
            <span class="text-xs font-semibold text-brand opacity-0 transition group-hover:opacity-100">Log &rarr;</span>
        </div>
    </a>
    <div class="ledger-cell">
        <div class="flex items-start justify-between gap-2">
            <p class="stat-label">Total in queue today</p>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-hairline bg-surface-subtle text-meta">
                <x-icon name="list" class="h-4 w-4" />
            </span>
        </div>
        <div>
            <p class="text-2xl font-bold tracking-tight text-bright cs-num">{{ $queue->count() }}</p>
            <p class="mt-0.5 text-xs text-meta">Scheduled + walk-ins</p>
        </div>
    </div>
</div>

{{-- Next patient up spotlight --}}
@if ($nextPatient)
    <section class="mt-5 rounded-lg border border-brand-border bg-brand-tint/80 p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-4">
                <div class="flex h-14 min-w-14 shrink-0 flex-col items-center justify-center rounded-md bg-brand px-3 text-white">
                    <span class="font-mono text-[10px] font-semibold tracking-wider text-brand-tint uppercase">Next</span>
                    <span class="font-mono text-lg leading-tight font-bold cs-num">#{{ $nextPatient->queue_number }}</span>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-mono text-[11px] font-semibold tracking-wider text-brand uppercase">Next patient up</p>
                        @if ($nextPatient->patient->activeAllergies->isNotEmpty())
                            <span class="inline-flex items-center gap-1 rounded-full border border-danger-border bg-danger-tint px-2.5 py-0.5 text-[11px] font-semibold text-danger">
                                <x-icon name="exclamation-triangle" class="h-3 w-3" />
                                Allergies: {{ $nextPatient->patient->activeAllergies->pluck('allergen')->take(3)->implode(', ') }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-lg font-bold text-bright">
                        {{ $nextPatient->patient->user->name }}
                        <span class="font-mono text-xs font-normal text-meta">· {{ $nextPatient->patient->patient_number }}</span>
                    </p>
                    <p class="mt-0.5 text-sm text-read cs-num">
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
