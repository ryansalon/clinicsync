@props([
    'icon' => 'clipboard',
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <span class="empty-icon">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>

    <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-5">
            {{ $action }}
        </div>
    @endisset
</div>
