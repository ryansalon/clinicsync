@props([
    'label',
    'value',
    'icon' => null,
    'hint' => null,
    'tone' => 'brand',
])

@php
    $tones = [
        'slate' => 'bg-surface-subtle text-meta border-hairline',
        'teal' => 'bg-brand-tint text-brand border-brand-border',
        'brand' => 'bg-brand-tint text-brand border-brand-border',
        'sky' => 'bg-brand-tint text-brand border-brand-border',
        'indigo' => 'bg-brand-tint text-brand border-brand-border',
        'violet' => 'bg-brand-tint text-brand border-brand-border',
        'amber' => 'bg-warn-tint text-warn border-warn-border',
        'rose' => 'bg-danger-tint text-danger border-danger-border',
        'emerald' => 'bg-ok-tint text-ok border-ok-border',
    ];

    $toneClass = $tones[$tone] ?? $tones['brand'];
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
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border {{ $toneClass }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    @endif
</div>
