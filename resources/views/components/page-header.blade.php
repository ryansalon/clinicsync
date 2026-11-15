@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        @isset($breadcrumb)
            {{ $breadcrumb }}
        @endisset

        <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ $title }}</h1>

        @if ($description)
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        @endif

        {{ $slot }}
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-end gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
