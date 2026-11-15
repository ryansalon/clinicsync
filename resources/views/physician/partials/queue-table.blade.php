<div class="card">
    <div class="card-header flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold tracking-tight text-[#14201E]">Consultation queue</h2>
            <p class="mt-0.5 text-xs text-[#465854] cs-num">{{ now()->isoFormat('dddd, MMM D, YYYY') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="chip border-[#B5DCD4] bg-[#E6F4F1] text-[#0F766E]">
                <span class="h-2 w-2 rounded-full bg-[#0F766E]" aria-hidden="true"></span>
                <span>Live updates</span>
                <span data-poll-status="live-physician-queue" class="font-mono text-[11px] text-[#465854]">· Auto</span>
            </span>
            <span class="chip cs-num">
                <x-icon name="users" class="h-4 w-4 text-[#465854]" />
                {{ $queue->count() }} in queue
            </span>
        </div>
    </div>

    {{-- Mobile stacked cards (below md) --}}
    <div class="divide-y divide-[#DCE3E1] md:hidden">
        @forelse ($queue as $appointment)
            <article class="space-y-3 p-4">
                <div class="flex items-center justify-between gap-2">
                    <span class="cs-num font-mono text-base font-semibold text-[#0F766E]">#{{ $appointment->queue_number }}</span>
                    <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                </div>

                <div class="flex items-start gap-3">
                    <x-avatar :name="$appointment->patient->user->name" size="sm" />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <p class="font-medium text-[#14201E]">{{ $appointment->patient->user->name }}</p>
                            @if ($appointment->patient->activeAllergies->isNotEmpty())
                                <span class="rounded-full border border-rose-300 bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-900"
                                      title="{{ $appointment->patient->activeAllergies->pluck('allergen')->implode(', ') }}">
                                    Allergies: {{ $appointment->patient->activeAllergies->pluck('allergen')->take(2)->implode(', ') }}@if ($appointment->patient->activeAllergies->count() > 2) +{{ $appointment->patient->activeAllergies->count() - 2 }}@endif
                                </span>
                            @endif
                        </div>
                        <p class="font-mono text-xs text-[#465854]">{{ $appointment->patient->patient_number }}</p>
                        <p class="mt-1 cs-num text-xs text-[#243330]">
                            {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                        </p>
                        @if ($appointment->chief_complaint)
                            <p class="mt-1 text-xs font-medium text-[#14201E]">
                                <span class="font-normal text-[#465854]">Complaint:</span> {{ $appointment->chief_complaint }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="border-t border-[#DCE3E1] pt-2">
                    @if (in_array($appointment->status, ['pending', 'confirmed'], true))
                        <a href="{{ route('physician.consultation', $appointment) }}" class="btn btn-primary btn-sm w-full">
                            Start consultation
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    @elseif ($appointment->status === 'completed')
                        @if ($appointment->encounter)
                            <a href="{{ route('physician.encounters.show', $appointment->encounter) }}" class="btn btn-secondary btn-sm w-full">
                                <x-icon name="document" class="h-4 w-4" />
                                View encounter
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 py-1 text-xs font-medium text-[#0F766E]">
                                <x-icon name="check-circle" class="h-4 w-4" />
                                Done
                            </span>
                        @endif
                    @else
                        <span class="text-xs text-[#465854]">—</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="p-8">
                <x-empty icon="clock" title="No patients are queued"
                         description="No patients are queued for today. New arrivals will appear here as they check in.">
                    <x-slot:action>
                        <a href="{{ route('physician.availability.index') }}" class="btn btn-secondary btn-sm">
                            <x-icon name="calendar" class="h-4 w-4" />
                            Manage availability
                        </a>
                    </x-slot:action>
                </x-empty>
            </div>
        @endforelse
    </div>

    {{-- Desktop table (md and up) --}}
    <div class="hidden overflow-x-auto md:block">
        @if ($queue->isNotEmpty())
            <table class="w-full text-left">
                <caption class="sr-only">Today's physician consultation queue</caption>
                <thead>
                <tr>
                    <th scope="col" class="table-head">Queue #</th>
                    <th scope="col" class="table-head">Patient</th>
                    <th scope="col" class="table-head">Scheduled time</th>
                    <th scope="col" class="table-head">Complaint</th>
                    <th scope="col" class="table-head">Status</th>
                    <th scope="col" class="table-head text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($queue as $appointment)
                    <tr class="table-row">
                        <td class="table-cell cs-num">
                            <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border border-[#B5DCD4] bg-[#E6F4F1] px-2.5 font-mono text-sm font-bold text-[#0F766E]">
                                #{{ $appointment->queue_number }}
                            </span>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$appointment->patient->user->name" size="sm" />
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <p class="font-medium text-[#14201E]">{{ $appointment->patient->user->name }}</p>
                                        @if ($appointment->patient->activeAllergies->isNotEmpty())
                                            <span class="rounded-full border border-rose-300 bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-900"
                                                  title="{{ $appointment->patient->activeAllergies->pluck('allergen')->implode(', ') }}">
                                                Allergies: {{ $appointment->patient->activeAllergies->pluck('allergen')->take(2)->implode(', ') }}@if ($appointment->patient->activeAllergies->count() > 2) +{{ $appointment->patient->activeAllergies->count() - 2 }}@endif
                                            </span>
                                        @endif
                                    </div>
                                    <p class="font-mono text-xs text-[#465854]">{{ $appointment->patient->patient_number }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell cs-num whitespace-nowrap">
                            {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                        </td>
                        <td class="table-cell">
                            {{ $appointment->chief_complaint ?: '—' }}
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                        </td>
                        <td class="table-cell text-right">
                            @if (in_array($appointment->status, ['pending', 'confirmed'], true))
                                <a href="{{ route('physician.consultation', $appointment) }}" class="btn btn-primary btn-sm">
                                    Start consultation
                                    <x-icon name="arrow-right" class="h-4 w-4" />
                                </a>
                            @elseif ($appointment->status === 'completed')
                                @if ($appointment->encounter)
                                    <a href="{{ route('physician.encounters.show', $appointment->encounter) }}" class="btn btn-secondary btn-sm">
                                        <x-icon name="document" class="h-4 w-4" />
                                        View encounter
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-[#0F766E]">
                                        <x-icon name="check-circle" class="h-4 w-4" />
                                        Done
                                    </span>
                                @endif
                            @else
                                <span class="text-[#465854]">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <div class="p-8">
                <x-empty icon="clock" title="No patients are queued"
                         description="No patients are queued for today. New arrivals will appear here as they check in.">
                    <x-slot:action>
                        <a href="{{ route('physician.availability.index') }}" class="btn btn-secondary btn-sm">
                            <x-icon name="calendar" class="h-4 w-4" />
                            Manage availability
                        </a>
                    </x-slot:action>
                </x-empty>
            </div>
        @endif
    </div>
</div>
