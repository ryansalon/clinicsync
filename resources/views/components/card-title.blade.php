@props([
    'description' => null,
])

<div>
    <h3 {{ $attributes->merge(['class' => 'text-sm font-semibold tracking-tight text-slate-900']) }}>
        {{ $slot }}
    </h3>
    @if ($description)
        <p class="mt-0.5 text-xs text-slate-500">{{ $description }}</p>
    @endif
</div>
