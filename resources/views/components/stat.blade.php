@props([
    'label',
    'value',
    'icon' => null,
    'hint' => null,
    'tone' => 'teal',
])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-600',
        'teal' => 'bg-teal-50 text-teal-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
    ];

    $toneClass = $tones[$tone] ?? $tones['teal'];
@endphp

<div {{ $attributes->merge(['class' => 'stat flex items-start justify-between gap-4']) }}>
    <div class="min-w-0">
        <p class="stat-label">{{ $label }}</p>
        <p class="stat-value">{{ $value }}</p>

        @if ($hint)
            <p class="stat-hint">{{ $hint }}</p>
        @endif

        {{ $slot }}
    </div>

    @if ($icon)
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $toneClass }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    @endif
</div>
