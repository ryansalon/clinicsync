{{-- Caseload at a glance --}}
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    <x-stat label="Waiting now" :value="$waiting->count()" icon="clock" tone="amber" />
    <x-stat label="Completed today" :value="$completedToday" icon="check-circle" tone="emerald" />
    <x-stat label="Encounters recorded" :value="$encounterCount" icon="clipboard" tone="indigo" />
    <x-stat label="Total in queue today" :value="$queue->count()" icon="users" tone="teal" />
</div>

{{-- Next patient up spotlight --}}
@if ($nextPatient)
    <section class="mt-6 rounded-2xl border border-teal-200/90 bg-gradient-to-br from-teal-50/90 via-white to-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_8px_20px_-10px_rgba(13,148,136,0.16)]">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-4">
                <div class="flex h-14 min-w-14 shrink-0 flex-col items-center justify-center rounded-2xl bg-teal-600 px-3 text-white shadow-sm">
                    <span class="text-[10px] font-semibold tracking-wider text-teal-100 uppercase">Next</span>
                    <span class="font-mono text-lg leading-tight font-bold">#{{ $nextPatient->queue_number }}</span>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-xs font-semibold tracking-wider text-teal-700 uppercase">Next patient up</p>
                        @if ($nextPatient->patient->activeAllergies->isNotEmpty())
                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-semibold text-rose-800">
                                <x-icon name="exclamation-triangle" class="h-3 w-3" />
                                {{ $nextPatient->patient->activeAllergies->count() }} active {{ \Illuminate\Support\Str::plural('allergy', $nextPatient->patient->activeAllergies->count()) }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-lg font-semibold text-slate-900">
                        {{ $nextPatient->patient->user->name }}
                        <span class="font-mono text-xs font-normal text-slate-500">· {{ $nextPatient->patient->patient_number }}</span>
                    </p>
                    <p class="mt-0.5 text-sm text-slate-600">
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
<div class="mt-6">
    @include('physician.partials.queue-table')
</div>
