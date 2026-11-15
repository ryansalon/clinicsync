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
        <section class="mb-6 overflow-hidden rounded-lg border border-[#0F766E]/25 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-4 bg-[#E6F4F1] px-5 py-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 min-w-14 shrink-0 flex-col items-center justify-center rounded-md bg-[#0F766E] px-3 text-white">
                        <span class="text-[10px] font-semibold tracking-wider text-[#E6F4F1] uppercase">Queue</span>
                        <span class="cs-num font-mono text-xl leading-tight font-bold">#{{ $queueAppointment->queue_number }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-[#0F766E]">
                            In today's queue · {{ $queueAppointment->physician?->user?->name ? 'Dr. '.$queueAppointment->physician->user->name : 'Clinic physician' }}
                        </p>
                        <p class="cs-num mt-0.5 text-lg font-semibold tracking-tight text-[#14201E] sm:text-xl">
                            Queue #{{ $queueAppointment->queue_number }}
                            <span class="text-sm font-medium text-[#465854]">
                                · {{ $queueStatus['waitingAhead'] === 0 ? 'You are next' : $queueStatus['waitingAhead'].' '.\Illuminate\Support\Str::plural('patient', $queueStatus['waitingAhead']).' ahead' }}
                            </span>
                        </p>
                        <p class="cs-num mt-0.5 text-xs text-[#465854]">
                            @if ($queueStatus['waitingAhead'] === 0)
                                Stay near the reception waiting area — your turn is next.
                            @else
                                Estimated wait: <strong class="font-semibold text-[#14201E]">~{{ $queueStatus['estimatedMinutes'] }} min</strong>
                            @endif
                            @if ($queueStatus['nowServing'] !== null)
                                · Now serving <strong class="font-semibold text-[#14201E]">#{{ $queueStatus['nowServing'] }}</strong>
                            @endif
                            · Slot {{ \Illuminate\Support\Carbon::parse($queueAppointment->start_time)->isoFormat('h:mm A') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="cs-num text-xs text-[#465854]" data-poll-status="live-queue-status">Live</span>
                    <a class="btn btn-secondary btn-sm" href="{{ route('patient.appointments.show', $queueAppointment) }}">
                        <x-icon name="eye" class="h-4 w-4" />
                        View visit
                    </a>
                </div>
            </div>

            <div class="border-t border-[#DCE3E1] px-5 py-3">
                <ol class="grid grid-cols-5 gap-2 text-center" aria-label="Visit progress">
                    @foreach ($pipelineSteps as $step)
                        <li class="flex flex-col items-center gap-1">
                            <span class="h-1.5 w-full rounded-full {{ $step['done'] ? 'bg-[#0F766E]' : ($step['current'] ? 'bg-[#0F766E]' : 'bg-[#DCE3E1]') }}"></span>
                            <span class="text-[11px] {{ $step['current'] ? 'font-semibold text-[#0F766E]' : ($step['done'] ? 'font-medium text-[#14201E]' : 'text-[#465854]') }}">
                                {{ $step['label'] }}
                            </span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @elseif ($state === 'called')
        <section class="mb-6 overflow-hidden rounded-lg border border-[#0D5D57] bg-[#0F766E] text-white" aria-live="assertive">
            <div class="flex flex-wrap items-center justify-between gap-4 p-5">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-white text-[#0F766E]">
                        <x-icon name="bell" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold tracking-wider text-[#E6F4F1] uppercase">Called for consultation</p>
                        <p class="cs-num mt-0.5 text-xl font-semibold tracking-tight text-white sm:text-2xl">
                            Queue #{{ $queueAppointment->queue_number }} — proceed to the clinic room
                        </p>
                        <p class="cs-num mt-0.5 text-sm text-[#E6F4F1]">
                            {{ $queueAppointment->physician?->user?->name ? 'Dr. '.$queueAppointment->physician->user->name : 'Attending physician' }}
                            · Scheduled {{ \Illuminate\Support\Carbon::parse($queueAppointment->start_time)->isoFormat('h:mm A') }}
                        </p>
                    </div>
                </div>
                <a class="btn bg-white text-[#0F766E] hover:bg-[#F4F7F6]"
                   href="{{ route('patient.appointments.show', $queueAppointment) }}">
                    <x-icon name="eye" class="h-4 w-4" />
                    View visit
                </a>
            </div>
        </section>
    @elseif ($state === 'completed')
        <section class="mb-6 overflow-hidden rounded-lg border border-[#DCE3E1] bg-white">
            <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3.5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-[#E6F4F1] text-[#0F766E]">
                        <x-icon name="check-circle" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-[#0F766E]">Visit complete</p>
                        <p class="cs-num mt-0.5 text-base font-semibold text-[#14201E]">
                            Your consultation today has finished — queue #{{ $queueAppointment->queue_number }}.
                        </p>
                        <p class="mt-0.5 text-xs text-[#465854]">
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
