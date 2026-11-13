<div class="card" aria-live="polite">
    <div class="card-header flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold tracking-tight text-slate-900">Consultation queue</h3>
            <p class="mt-0.5 text-xs text-slate-500">{{ now()->isoFormat('dddd, MMM D, YYYY') }}</p>
        </div>
        <span class="chip">
            <x-icon name="users" class="h-4 w-4 text-slate-400" />
            {{ $queue->count() }} in queue
        </span>
    </div>

    {{-- Mobile stacked cards (below md) --}}
    <div class="divide-y divide-slate-100 md:hidden">
        @forelse ($queue as $appointment)
            <article class="p-4 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-base font-semibold text-slate-900">#{{ $appointment->queue_number }}</span>
                    <span class="badge badge-{{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                </div>

                <div class="flex items-start gap-3">
                    <x-avatar :name="$appointment->patient->user->name" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-slate-900">{{ $appointment->patient->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $appointment->patient->patient_number }}</p>
                        <p class="mt-1 text-xs text-slate-600">
                            {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->isoFormat('h:mm A') }} – {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->isoFormat('h:mm A') }}
                        </p>
                        @if ($appointment->chief_complaint)
                            <p class="mt-1 text-xs text-slate-700 font-medium">
                                <span class="text-slate-400 font-normal">Complaint:</span> {{ $appointment->chief_complaint }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    @if (in_array($appointment->status, ['pending', 'confirmed'], true))
                        <a href="{{ route('physician.consultation', $appointment) }}" class="btn btn-primary btn-sm w-full">
                            Start consultation
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    @elseif ($appointment->status === 'completed')
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 py-1">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            Done
                        </span>
                    @else
                        <span class="text-xs text-slate-400">—</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="p-8">
                <x-empty icon="clock" title="No patients are queued"
                         description="No patients are queued for today. New arrivals will appear here as they check in." />
            </div>
        @endforelse
    </div>

    {{-- Desktop table (md and up) --}}
    <div class="hidden overflow-x-auto md:block">
        <table class="w-full text-left">
            <thead>
                <tr>
                    <th class="table-head">Queue #</th>
                    <th class="table-head">Patient</th>
                    <th class="table-head">Scheduled time</th>
                    <th class="table-head">Complaint</th>
                    <th class="table-head">Status</th>
                    <th class="table-head text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($queue as $appointment)
                    <tr class="table-row">
                        <td class="table-cell">
                            <span class="text-base font-semibold text-slate-900">#{{ $appointment->queue_number }}</span>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$appointment->patient->user->name" size="sm" />
                                <div class="min-w-0">
                                    <p class="font-medium text-slate-900">{{ $appointment->patient->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $appointment->patient->patient_number }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell whitespace-nowrap">
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
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">
                                    <x-icon name="check-circle" class="h-4 w-4" />
                                    Done
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12">
                            <x-empty icon="clock" title="No patients are queued"
                                     description="No patients are queued for today. New arrivals will appear here as they check in." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
