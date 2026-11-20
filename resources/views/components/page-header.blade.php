@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @isset($breadcrumb)
            {{ $breadcrumb }}
        @endisset

        <h1 class="text-xl font-bold tracking-[-0.025em] text-bright sm:text-2xl">{{ $title }}</h1>

        @if ($description)
            <p class="mt-1 text-sm text-meta">{{ $description }}</p>
        @endif

        {{ $slot }}
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-end gap-2.5">
            {{ $actions }}
        </div>
    @endisset
</div>
