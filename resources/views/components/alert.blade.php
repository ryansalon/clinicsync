@props([
    'type' => 'info',
    'title' => null,
])

@php
    $types = [
        'success' => ['alert-success', 'check-circle'],
        'error' => ['alert-error', 'exclamation-triangle'],
        'warning' => ['alert-warning', 'exclamation-triangle'],
        'info' => ['alert-info', 'information-circle'],
    ];

    [$typeClass, $icon] = $types[$type] ?? $types['info'];
@endphp

<div {{ $attributes->merge(['class' => "alert {$typeClass}"]) }} role="alert">
    <x-icon :name="$icon" class="mt-0.5 h-5 w-5 shrink-0" />

    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif

        <div class="{{ $title ? 'mt-0.5' : '' }}">
            {{ $slot }}
        </div>
    </div>

    @if (isset($dismiss) && $dismiss)
        <button type="button" class="alert-dismiss -m-1.5 -mr-2"
                data-flash-dismiss aria-label="Dismiss">
            <x-icon name="x" class="h-4 w-4" />
        </button>
    @endif
</div>
