@props([
    'icon' => 'clipboard',
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <span class="empty-icon">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>

    <h2 class="mt-4 text-sm font-semibold text-[#14201E]">{{ $title }}</h2>

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-[#465854]">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-5">
            {{ $action }}
        </div>
    @endisset
</div>
