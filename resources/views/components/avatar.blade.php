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
        'bg-brand-tint text-brand ring-brand-border',
        'bg-rail text-bright ring-hairline-2',
        'bg-ok-tint text-ok ring-ok-border',
        'bg-warn-tint text-warn ring-warn-border',
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
    <img {{ $attributes->merge(['class' => "avatar {$sizeClass} bg-rail object-cover ring-hairline"]) }}
         src="{{ $photo }}" alt="{{ $name }}" title="{{ $name }}"
         loading="lazy" width="{{ $pixelSize }}" height="{{ $pixelSize }}">
@else
    <span {{ $attributes->merge(['class' => "avatar {$sizeClass} {$palette}"]) }} title="{{ $name }}">{{ $initials }}</span>
@endif
