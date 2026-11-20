@if ($patient && $queueStatus)
    @php
        $queueAppointment = $queueStatus['appointment'];
        $state = $queueStatus['state'];
        $pipelineSteps = [
            ['key' => 'scheduled', 'label' => 'Scheduled', 'done' => true, 'current' => false],
            ['key' => 'waiting', 'label' => 'Waiting', 'done' => in_array($state, ['called', 'completed'], true), 'current' => $state === 'waiting'],
            ['key' => 'called', 'label' => 'Called', 'done' => $state === 'completed', 'current' => $state === 'called'],
            ['key' => 'consult', 'label' => 'Consult', 'done' => $state === 'completed', 'current' => false],
            ['key' => 'completed', 'label' => 'Completed', 'done' => $state === 'completed', 'current' => $state === 'completed'],
        ];
    @endphp

    @if ($state === 'waiting')
        <section class="mb-5 overflow-hidden rounded-lg border border-brand-border bg-surface">
            <div class="flex flex-wrap items-center justify-between gap-4 bg-brand-tint/80 px-5 py-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 min-w-14 shrink-0 flex-col items-center justify-center rounded-md bg-brand px-3 text-white">
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-brand-tint uppercase">Queue</span>
                        <span class="cs-num font-mono text-xl leading-tight font-bold">#{{ $queueAppointment->queue_number }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-mono text-[11px] font-semibold tracking-wider text-brand uppercase">
                            In today's queue · {{ $queueAppointment->physician?->user?->name ? 'Dr. '.$queueAppointment->physician->user->name : 'Clinic physician' }}
                        </p>
                        <p class="cs-num mt-0.5 text-lg font-bold tracking-tight text-bright sm:text-xl">
                            Queue #{{ $queueAppointment->queue_number }}
                            <span class="text-sm font-medium text-meta">
                                · {{ $queueStatus['waitingAhead'] === 0 ? 'You are next' : $queueStatus['waitingAhead'].' '.\Illuminate\Support\Str::plural('patient', $queueStatus['waitingAhead']).' ahead' }}
                            </span>
                        </p>
                        <p class="cs-num mt-0.5 text-xs text-meta">
                            @if ($queueStatus['waitingAhead'] === 0)
                                Stay near the reception waiting area — your turn is next.
                            @else
                                Estimated wait: <strong class="font-semibold text-bright">~{{ $queueStatus['estimatedMinutes'] }} min</strong>
                            @endif
                            @if ($queueStatus['nowServing'] !== null)
                                · Now serving <strong class="font-mono font-semibold text-bright">#{{ $queueStatus['nowServing'] }}</strong>
                            @endif
                            · Slot {{ \Illuminate\Support\Carbon::parse($queueAppointment->start_time)->isoFormat('h:mm A') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="cs-num font-mono text-xs text-meta" data-poll-status="live-queue-status">Live</span>
                    <a class="btn btn-secondary btn-sm" href="{{ route('patient.appointments.show', $queueAppointment) }}">
                        <x-icon name="eye" class="h-4 w-4" />
                        View visit
                    </a>
                </div>
            </div>

            <div class="border-t border-hairline px-5 py-3">
                <ol class="grid grid-cols-5 gap-2 text-center" aria-label="Visit progress">
                    @foreach ($pipelineSteps as $step)
                        <li class="flex flex-col items-center gap-1">
                            <span class="h-1.5 w-full rounded-full {{ $step['done'] || $step['current'] ? 'bg-brand' : 'bg-hairline' }}"></span>
                            <span class="text-[11px] {{ $step['current'] ? 'font-semibold text-brand' : ($step['done'] ? 'font-medium text-bright' : 'text-meta') }}">
                                {{ $step['label'] }}
                            </span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @elseif ($state === 'called')
        <section class="mb-5 overflow-hidden rounded-lg border border-brand-strong bg-brand text-white" aria-live="assertive">
            <div class="flex flex-wrap items-center justify-between gap-4 p-5">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-surface text-brand">
                        <x-icon name="bell" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="font-mono text-xs font-semibold tracking-wider text-brand-tint uppercase">Called for consultation</p>
                        <p class="cs-num mt-0.5 text-xl font-bold tracking-tight text-white sm:text-2xl">
                            Queue #{{ $queueAppointment->queue_number }} — proceed to the clinic room
                        </p>
                        <p class="cs-num mt-0.5 text-sm text-brand-tint">
                            {{ $queueAppointment->physician?->user?->name ? 'Dr. '.$queueAppointment->physician->user->name : 'Attending physician' }}
                            · Scheduled {{ \Illuminate\Support\Carbon::parse($queueAppointment->start_time)->isoFormat('h:mm A') }}
                        </p>
                    </div>
                </div>
                <a class="btn border-surface bg-surface text-brand hover:bg-canvas"
                   href="{{ route('patient.appointments.show', $queueAppointment) }}">
                    <x-icon name="eye" class="h-4 w-4" />
                    View visit
                </a>
            </div>
        </section>
    @elseif ($state === 'completed')
        <section class="mb-5 overflow-hidden rounded-lg border border-ok-border bg-surface">
            <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3.5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md border border-ok-border bg-ok-tint text-ok">
                        <x-icon name="check-circle" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="font-mono text-[11px] font-semibold tracking-wider text-ok uppercase">Visit complete</p>
                        <p class="cs-num mt-0.5 text-base font-semibold text-bright">
                            Your consultation today has finished — queue #{{ $queueAppointment->queue_number }}.
                        </p>
                        <p class="mt-0.5 text-xs text-meta">
                            Your clinical summary and prescriptions are ready in Medical records.
                        </p>
                    </div>
                </div>
                <a class="btn btn-secondary btn-sm" href="{{ route('patient.records') }}">
                    <x-icon name="document" class="h-4 w-4" />
                    View records
                </a>
            </div>
        </section>
    @endif
@endif
