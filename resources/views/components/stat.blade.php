@props([
    'label',
    'value',
    'icon' => null,
    'hint' => null,
    'tone' => 'teal',
])

@php
    $tones = [
        'slate' => 'bg-[#EFF4F2] text-[#465854] border-[#DCE3E1]',
        'teal' => 'bg-[#E6F4F1] text-[#0F766E] border-[#B5DCD4]',
        'sky' => 'bg-sky-50 text-sky-900 border-sky-200',
        'indigo' => 'bg-indigo-50 text-indigo-900 border-indigo-200',
        'violet' => 'bg-violet-50 text-violet-900 border-violet-200',
        'amber' => 'bg-amber-50 text-amber-900 border-amber-200',
        'rose' => 'bg-rose-50 text-rose-900 border-rose-200',
        'emerald' => 'bg-emerald-50 text-emerald-900 border-emerald-200',
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
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border {{ $toneClass }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    @endif
</div>
