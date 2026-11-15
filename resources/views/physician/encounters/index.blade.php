@extends('layouts.app')

@section('title', 'Encounter log')

@section('content')
    <x-page-header title="Encounter log" description="Consultations recorded under your clinical practice.">
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route('physician.dashboard') }}">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Back to dashboard
            </a>
        </x-slot:actions>
    </x-page-header>

    @php
        $filterChips = $search !== ''
            ? [['label' => "Search: {$search}", 'url' => route('physician.encounters.index')]]
            : [];
    @endphp

    <x-filter-bar :action="route('physician.encounters.index')" submit-label="Search" :chips="$filterChips">
        <x-slot:fields>
            <div class="flex-1 sm:max-w-md">
                <label for="q" class="label">Search encounters</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#465854]">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input id="q" type="search" name="q" value="{{ $search }}" class="input pl-9"
                           placeholder="Patient name, patient #, encounter #, diagnosis">
                </div>
            </div>
        </x-slot:fields>
    </x-filter-bar>

    <section class="card mt-6">
        <div class="card-header flex items-center justify-between gap-2">
            <x-card-title>Recorded consultations</x-card-title>
            <span class="cs-num text-xs text-[#465854]">
                {{ $encounters->total() }} {{ \Illuminate\Support\Str::plural('encounter', $encounters->total()) }}
            </span>
        </div>

        <div class="card-body p-0">
            {{-- Mobile stacked cards --}}
            <div class="divide-y divide-[#DCE3E1] md:hidden">
                @forelse ($encounters as $encounter)
                    <article class="space-y-2.5 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="cs-num font-mono text-xs font-semibold text-[#0F766E]">
                                    {{ $encounter->encounter_number }}
                                </p>
                                <p class="mt-0.5 text-sm font-medium text-[#14201E]">
                                    {{ $encounter->patient?->user?->name ?? 'Unknown patient' }}
                                </p>
                                <p class="cs-num text-xs text-[#465854]">
                                    {{ $encounter->patient?->patient_number ?? '—' }} · {{ $encounter->encountered_at?->isoFormat('MMM D, YYYY') ?? '—' }}
                                </p>
                            </div>
                            <span class="badge badge-{{ $encounter->status }} shrink-0">
                                {{ \Illuminate\Support\Str::headline($encounter->status) }}
                            </span>
                        </div>

                        <p class="text-sm text-[#14201E]">
                            {{ $encounter->diagnosis ?: 'Diagnosis pending' }}
                            @if ($encounter->diagnosis_code)
                                <span class="cs-num font-mono text-xs text-[#465854]">({{ $encounter->diagnosis_code }})</span>
                            @endif
                        </p>

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                            <span class="cs-num text-xs text-[#465854]">
                                {{ $encounter->vitals ? 'Vitals recorded' : 'No vitals' }} · {{ $encounter->prescriptions->count() }} Rx
                            </span>
                            <a class="btn btn-secondary btn-sm" href="{{ route('physician.encounters.show', $encounter) }}">
                                <x-icon name="eye" class="h-4 w-4" />
                                Open chart
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="p-6">
                        <x-empty icon="clipboard" title="No encounters matched your query."
                                 description="Completed or in-progress consultations will be listed here." />
                    </div>
                @endforelse
            </div>

            {{-- Desktop clinical ledger --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full">
                    <caption class="sr-only">Recorded physician encounters</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="table-head">Encounter #</th>
                            <th scope="col" class="table-head">Date</th>
                            <th scope="col" class="table-head">Patient</th>
                            <th scope="col" class="table-head">Diagnosis</th>
                            <th scope="col" class="table-head">Vitals</th>
                            <th scope="col" class="table-head">Prescriptions</th>
                            <th scope="col" class="table-head">Status</th>
                            <th scope="col" class="table-head text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#DCE3E1]">
                        @forelse ($encounters as $encounter)
                            <tr class="table-row">
                                <td class="table-cell cs-num font-mono text-xs font-semibold text-[#0F766E] whitespace-nowrap">
                                    {{ $encounter->encounter_number }}
                                </td>
                                <td class="table-cell cs-num whitespace-nowrap text-[#465854]">
                                    {{ $encounter->encountered_at?->isoFormat('MMM D, YYYY') ?? '—' }}
                                </td>
                                <td class="table-cell">
                                    <div class="flex items-center gap-2.5">
                                        @if ($encounter->patient?->user?->name)
                                            <x-avatar :name="$encounter->patient->user->name" size="sm" />
                                        @endif
                                        <div class="min-w-0">
                                            @if ($encounter->patient)
                                                <a class="font-medium text-[#14201E] hover:text-[#0F766E] hover:underline"
                                                   href="{{ route('physician.patients.show', $encounter->patient) }}">
                                                    {{ $encounter->patient->user?->name ?? 'Unknown' }}
                                                </a>
                                            @else
                                                <span class="font-medium text-[#14201E]">Unknown</span>
                                            @endif
                                            <p class="cs-num font-mono text-xs text-[#465854]">
                                                {{ $encounter->patient?->patient_number ?? '—' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <span class="text-[#14201E]">{{ $encounter->diagnosis ?: 'Diagnosis pending' }}</span>
                                    @if ($encounter->diagnosis_code)
                                        <span class="cs-num ml-1 font-mono text-xs text-[#465854]">{{ $encounter->diagnosis_code }}</span>
                                    @endif
                                </td>
                                <td class="table-cell whitespace-nowrap text-xs text-[#465854]">
                                    {{ $encounter->vitals ? 'Recorded' : '—' }}
                                </td>
                                <td class="table-cell cs-num whitespace-nowrap text-[#14201E]">
                                    {{ $encounter->prescriptions->count() }}
                                </td>
                                <td class="table-cell whitespace-nowrap">
                                    <span class="badge badge-{{ $encounter->status }}">
                                        {{ \Illuminate\Support\Str::headline($encounter->status) }}
                                    </span>
                                </td>
                                <td class="table-cell whitespace-nowrap text-right">
                                    <a class="btn btn-secondary btn-sm" href="{{ route('physician.encounters.show', $encounter) }}">
                                        <x-icon name="eye" class="h-4 w-4" />
                                        Open chart
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-10" colspan="8">
                                    <x-empty icon="clipboard" title="No encounters matched your query."
                                             description="Completed or in-progress consultations will be listed here." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($encounters->hasPages())
            <div class="border-t border-[#DCE3E1] px-5 py-3.5">
                {{ $encounters->links() }}
            </div>
        @endif
    </section>
@endsection
