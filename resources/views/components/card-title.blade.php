@props([
    'description' => null,
])

<div>
    <h2 {{ $attributes->merge(['class' => 'text-sm font-semibold tracking-tight text-bright']) }}>
        {{ $slot }}
    </h2>
    @if ($description)
        <p class="mt-0.5 text-xs text-meta">{{ $description }}</p>
    @endif
</div>
