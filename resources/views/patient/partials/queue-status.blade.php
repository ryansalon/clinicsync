@if ($patient && $queueStatus)
    @php($queueAppointment = $queueStatus['appointment'])
    @if ($queueStatus['state'] === 'waiting')
        <section class="mb-6 overflow-hidden rounded-2xl border border-teal-200/90 bg-gradient-to-br from-teal-50/90 via-white to-white shadow-[0_1px_2px_rgba(15,23,42,0.04),0_8px_20px_-10px_rgba(13,148,136,0.18)]">
            <div class="flex flex-wrap items-center gap-5 p-5 sm:p-6">
                <div class="flex h-16 min-w-16 shrink-0 flex-col items-center justify-center rounded-2xl bg-teal-600 px-3 text-white shadow-sm">
                    <span class="text-[10px] font-semibold tracking-wider text-teal-100 uppercase">Queue</span>
                    <span class="font-mono text-xl leading-tight font-bold">#{{ $queueAppointment->queue_number }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="inline-flex items-center gap-2 text-[11px] font-semibold tracking-wider text-teal-700 uppercase">
                        <span class="h-2 w-2 rounded-full bg-teal-500" aria-hidden="true"></span>
                        In today's queue · {{ $queueAppointment->physician?->user?->name ? 'Dr. '.$queueAppointment->physician->user->name : 'Physician' }}
                    </p>
                    <p class="mt-1 text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl" aria-live="polite">
                        Queue #{{ $queueAppointment->queue_number }}
                        <span class="text-base font-medium text-slate-500">
                            · {{ $queueStatus['waitingAhead'] === 0 ? 'You are next' : $queueStatus['waitingAhead'].' '.\Illuminate\Support\Str::plural('patient', $queueStatus['waitingAhead']).' ahead of you' }}
                        </span>
                    </p>
                    <p class="mt-1 text-sm text-slate-600">
                        @if ($queueStatus['waitingAhead'] === 0)
                            Please stay near the clinic — you're up shortly.
                        @else
                            Estimated wait: <span class="font-semibold text-slate-900">~{{ $queueStatus['estimatedMinutes'] }} minutes</span>.
                        @endif
                        @if ($queueStatus['nowServing'] !== null)
                            Now serving <span class="font-semibold text-slate-900">#{{ $queueStatus['nowServing'] }}</span> ·
                        @endif
                        Your slot: {{ \Illuminate\Support\Carbon::parse($queueAppointment->start_time)->isoFormat('h:mm A') }}.
                    </p>
                </div>
                <a class="btn btn-secondary" href="{{ route('patient.appointments.show', $queueAppointment) }}">
                    <x-icon name="eye" class="h-4 w-4" />
                    View appointment
                </a>
            </div>
        </section>
    @elseif ($queueStatus['state'] === 'called')
        <section class="mb-6 animate-fade-in overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50 shadow-sm" aria-live="assertive">
            <div class="flex flex-wrap items-center gap-5 p-5">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
                    <x-icon name="bell" class="h-7 w-7" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold tracking-wider text-emerald-700 uppercase">You've been called</p>
                    <p class="mt-1 text-2xl font-semibold tracking-tight text-emerald-950">
                        Queue #{{ $queueAppointment->queue_number }} — please proceed now
                    </p>
                    <p class="mt-1 text-sm text-emerald-800">
                        Go to the physician's clinic at {{ \Illuminate\Support\Carbon::parse($queueAppointment->start_time)->isoFormat('h:mm A') }}.
                    </p>
                </div>
                <a class="btn btn-secondary border-emerald-300 bg-white text-emerald-800 hover:bg-emerald-100"
                   href="{{ route('patient.appointments.show', $queueAppointment) }}">
                    <x-icon name="eye" class="h-4 w-4" />
                    View appointment
                </a>
            </div>
        </section>
    @elseif ($queueStatus['state'] === 'completed')
        <section class="mb-6 overflow-hidden rounded-xl border border-sky-200 bg-sky-50 shadow-sm">
            <div class="flex flex-wrap items-center gap-5 p-5">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-sky-600 text-white shadow-sm">
                    <x-icon name="check-circle" class="h-7 w-7" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold tracking-wider text-sky-700 uppercase">Visit complete</p>
                    <p class="mt-1 text-xl font-semibold tracking-tight text-sky-950">
                        Your consultation today has finished — queue #{{ $queueAppointment->queue_number }}.
                    </p>
                    <p class="mt-1 text-sm text-sky-800">
                        The consultation summary and any prescriptions are in your medical records.
                    </p>
                </div>
                <a class="btn btn-secondary border-sky-300 bg-white text-sky-800 hover:bg-sky-100"
                   href="{{ route('patient.records') }}">
                    <x-icon name="document" class="h-4 w-4" />
                    View records
                </a>
            </div>
        </section>
    @endif
@endif
