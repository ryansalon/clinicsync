@props([
    'action',
    'submitLabel' => 'Search',
    /** @var array<int, array{label: string, url: string}> $chips */
    'chips' => [],
])

{{-- Compact filter bar with debounced live search and integrated removable filter chips. --}}
<div class="mb-5 space-y-2.5">
    <form method="GET" action="{{ $action }}" data-search-form class="card">
        <div class="flex flex-col gap-3.5 p-3.5 sm:flex-row sm:items-end sm:px-4">
            {{ $fields ?? $slot }}

            <button type="submit" class="btn btn-secondary sm:shrink-0">
                <x-icon name="magnifying-glass" class="h-4 w-4" />
                {{ $submitLabel }}
            </button>
        </div>

        @if (count($chips) > 0)
            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-hairline bg-surface-subtle/60 px-4 py-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-[10px] font-semibold tracking-wider text-meta uppercase">Active</span>

                    @foreach ($chips as $chip)
                        <a href="{{ $chip['url'] }}"
                           class="chip min-h-8 bg-surface transition hover:border-brand hover:bg-brand-tint hover:text-brand focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
                           aria-label="Remove filter {{ $chip['label'] }}">
                            {{ $chip['label'] }}
                            <x-icon name="x" class="h-3.5 w-3.5" />
                        </a>
                    @endforeach
                </div>

                @if (count($chips) > 1)
                    <a href="{{ $action }}" class="text-xs font-semibold text-brand hover:underline">
                        Clear all
                    </a>
                @endif
            </div>
        @endif
    </form>
</div>
