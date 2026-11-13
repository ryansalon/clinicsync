@props([
    'name',
    'size' => 'md',
    /** Authorized URL to the photo, or null to fall back to initials. */
    'photo' => null,
])

@php
    $initials = collect(explode(' ', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');

    $palettes = [
        'bg-teal-100 text-teal-700',
        'bg-sky-100 text-sky-700',
        'bg-indigo-100 text-indigo-700',
        'bg-violet-100 text-violet-700',
        'bg-amber-100 text-amber-700',
        'bg-rose-100 text-rose-700',
        'bg-emerald-100 text-emerald-700',
    ];

    $palette = $palettes[abs(crc32($name)) % count($palettes)];

    $sizes = [
        'sm' => 'h-8 w-8 text-[11px]',
        'md' => 'h-9 w-9 text-xs',
        'lg' => 'h-12 w-12 text-sm',
    ];

    /** Pixel dimensions for img width/height attributes to prevent CLS. */
    $pixelSizes = [
        'sm' => 32,
        'md' => 36,
        'lg' => 48,
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $pixelSize = $pixelSizes[$size] ?? $pixelSizes['md'];
@endphp

@if ($photo)
    <img {{ $attributes->merge(['class' => "avatar {$sizeClass} bg-slate-100 object-cover"]) }}
         src="{{ $photo }}" alt="{{ $name }}" title="{{ $name }}"
         loading="lazy" width="{{ $pixelSize }}" height="{{ $pixelSize }}">
@else
    <span {{ $attributes->merge(['class' => "avatar {$sizeClass} {$palette}"]) }} title="{{ $name }}">{{ $initials }}</span>
@endif
