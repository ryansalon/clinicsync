@props([
    'description' => null,
])

<div>
    <h2 {{ $attributes->merge(['class' => 'text-sm font-semibold tracking-tight text-[#14201E]']) }}>
        {{ $slot }}
    </h2>
    @if ($description)
        <p class="mt-0.5 text-xs text-[#465854]">{{ $description }}</p>
    @endif
</div>
