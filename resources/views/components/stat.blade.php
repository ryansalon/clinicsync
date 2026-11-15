@props([
    'label',
    'value',
    'icon' => null,
    'hint' => null,
    'tone' => 'teal',
])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-200/80',
        'teal' => 'bg-teal-50 text-teal-700 ring-teal-200/70',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-200/70',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-200/70',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-200/70',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-200/70',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-200/70',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70',
    ];

    $bars = [
        'slate' => 'bg-slate-400',
        'teal' => 'bg-teal-500',
        'sky' => 'bg-sky-500',
        'indigo' => 'bg-indigo-500',
        'violet' => 'bg-violet-500',
        'amber' => 'bg-amber-500',
        'rose' => 'bg-rose-500',
        'emerald' => 'bg-emerald-500',
    ];

    $toneClass = $tones[$tone] ?? $tones['teal'];
    $barClass = $bars[$tone] ?? $bars['teal'];
@endphp

<div {{ $attributes->merge(['class' => 'stat flex items-start justify-between gap-4']) }}>
    <span class="pointer-events-none absolute inset-x-0 top-0 h-[3px] {{ $barClass }}" aria-hidden="true"></span>
    <div class="min-w-0">
        <p class="stat-label">{{ $label }}</p>
        <p class="stat-value">{{ $value }}</p>

        @if ($hint)
            <p class="stat-hint">{{ $hint }}</p>
        @endif

        {{ $slot }}
    </div>

    @if ($icon)
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset {{ $toneClass }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    @endif
</div>
