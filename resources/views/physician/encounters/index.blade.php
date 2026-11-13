@extends('layouts.app')

@section('title', 'Encounter log')

@section('content')
    <x-page-header title="Encounter log"
                   description="Every consultation you have recorded, newest first.">
    </x-page-header>

    @php
        $filterChips = [];
        if ($search !== '') {
            $filterChips[] = [
                'label' => '“'.$search.'”',
                'url' => route('physician.encounters.index'),
            ];
        }
    @endphp

    <x-filter-bar :action="route('physician.encounters.index')" submit-label="Search" :chips="$filterChips">
        <x-slot:fields>
            <div class="flex-1">
                <label for="q" class="label">Search encounters</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="magnifying-glass" class="h-4 w-4" />
                    </span>
                    <input id="q" name="q" type="search" value="{{ $search }}" class="input pl-10"
                           placeholder="Patient name, patient number, encounter # or diagnosis">
                </div>
            </div>
        </x-slot:fields>
    </x-filter-bar>

    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2">
            <x-card-title>Recorded encounters</x-card-title>
            <span class="text-xs text-slate-500">{{ $encounters->total() }} total</span>
        </div>

        {{-- Mobile card view (below md) --}}
        <div class="divide-y divide-slate-100 md:hidden">
            @forelse ($encounters as $encounter)
                <article class="p-4 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-semibold text-slate-900">{{ $encounter->encounter_number }}</span>
                        <span class="badge badge-{{ $encounter->status }}">{{ \Illuminate\Support\Str::headline($encounter->status) }}</span>
                    </div>

                    <div class="flex items-start gap-3">
                        <x-avatar :name="$encounter->patient->user->name" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-slate-900">{{ $encounter->patient->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $encounter->patient->patient_number }}</p>
                            <p class="mt-1 text-xs text-slate-700 font-medium truncate" title="{{ $encounter->diagnosis }}">
                                {{ $encounter->diagnosis }}
                                @if ($encounter->diagnosis_code)
                                    <span class="text-slate-400 font-normal">({{ $encounter->diagnosis_code }})</span>
                                @endif
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $encounter->encountered_at->isoFormat('MMM D, YYYY · h:mm A') }}
                                · {{ $encounter->prescriptions->count() }} Rx
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex justify-end">
                        <a href="{{ route('physician.encounters.show', $encounter) }}" class="btn btn-secondary btn-sm">
                            <x-icon name="eye" class="h-4 w-4" />
                            Open record
                        </a>
                    </div>
                </article>
            @empty
                <div class="p-8">
                    @if ($search !== '')
                        <x-empty icon="magnifying-glass" title="No encounters found"
                                 :description="'No encounters match “' . $search . '”. Try a different patient name, number or diagnosis.'" />
                    @else
                        <x-empty icon="clipboard" title="No encounters recorded yet"
                                 description="Encounters you record during consultations will appear here." />
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Desktop table (md and up) --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th class="table-head">Encounter #</th>
                        <th class="table-head">Date</th>
                        <th class="table-head">Patient</th>
                        <th class="table-head">Diagnosis</th>
                        <th class="table-head">Vitals</th>
                        <th class="table-head">Prescriptions</th>
                        <th class="table-head">Status</th>
                        <th class="table-head text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($encounters as $encounter)
                        <tr class="table-row">
                            <td class="table-cell whitespace-nowrap font-medium text-slate-900">
                                {{ $encounter->encounter_number }}
                            </td>
                            <td class="table-cell whitespace-nowrap">
                                {{ $encounter->encountered_at->isoFormat('dddd, MMM D, YYYY') }}
                                <span class="block text-xs text-slate-500">{{ $encounter->encountered_at->isoFormat('h:mm A') }}</span>
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$encounter->patient->user->name" size="sm" />
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-900">{{ $encounter->patient->user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $encounter->patient->patient_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-cell">
                                <div class="max-w-[16rem] truncate" title="{{ $encounter->diagnosis }}">
                                    {{ $encounter->diagnosis }}
                                    @if ($encounter->diagnosis_code)
                                        <span class="ml-1 text-xs font-medium text-slate-400">{{ $encounter->diagnosis_code }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="table-cell whitespace-nowrap">
                                @if ($encounter->vitals !== null)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">
                                        <x-icon name="check" class="h-4 w-4" />
                                        Recorded
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="table-cell">{{ $encounter->prescriptions->count() }}</td>
                            <td class="table-cell">
                                <span class="badge badge-{{ $encounter->status }}">{{ \Illuminate\Support\Str::headline($encounter->status) }}</span>
                            </td>
                            <td class="table-cell text-right">
                                <a href="{{ route('physician.encounters.show', $encounter) }}" class="btn btn-secondary btn-sm">
                                    <x-icon name="eye" class="h-4 w-4" />
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12">
                                @if ($search !== '')
                                    <x-empty icon="magnifying-glass" title="No encounters found"
                                             :description="'No encounters match “' . $search . '”. Try a different patient name, number or diagnosis.'" />
                                @else
                                    <x-empty icon="clipboard" title="No encounters recorded yet"
                                             description="Encounters you record during consultations will appear here." />
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($encounters->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $encounters->links() }}
            </div>
        @endif
    </div>
@endsection
