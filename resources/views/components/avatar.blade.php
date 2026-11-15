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
        'bg-[#E6F4F1] text-[#0F766E] ring-1 ring-[#0F766E]/15',
        'bg-[#EAEFED] text-[#14201E] ring-1 ring-[#DCE3E1]',
        'bg-[#EBF2F7] text-[#1B435E] ring-1 ring-[#1B435E]/15',
        'bg-[#F4F1E8] text-[#4E4226] ring-1 ring-[#4E4226]/15',
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
    <img {{ $attributes->merge(['class' => "avatar {$sizeClass} bg-[#EAEFED] object-cover ring-1 ring-[#DCE3E1]"]) }}
         src="{{ $photo }}" alt="{{ $name }}" title="{{ $name }}"
         loading="lazy" width="{{ $pixelSize }}" height="{{ $pixelSize }}">
@else
    <span {{ $attributes->merge(['class' => "avatar {$sizeClass} {$palette}"]) }} title="{{ $name }}">{{ $initials }}</span>
@endif
