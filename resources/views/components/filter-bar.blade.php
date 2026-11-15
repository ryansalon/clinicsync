@props([
    'action',
    'submitLabel' => 'Search',
    /** @var array<int, array{label: string, url: string}> $chips */
    'chips' => [],
])

{{-- Consistent filter bar: one card, one form, debounced live search on any text
     input, and removable chips for the filters that are currently applied. --}}
<div class="mb-6 space-y-3">
    @if (count($chips) > 0)
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold tracking-wider text-[#465854] uppercase">Filters</span>

            @foreach ($chips as $chip)
                <a href="{{ $chip['url'] }}"
                   class="chip min-h-9 transition hover:border-[#0F766E] hover:bg-[#E6F4F1] hover:text-[#0F766E] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0F766E]"
                   aria-label="Remove filter {{ $chip['label'] }}">
                    {{ $chip['label'] }}
                    <x-icon name="x" class="h-3.5 w-3.5" />
                </a>
            @endforeach
        </div>
    @endif

    <form method="GET" action="{{ $action }}" data-search-form class="card">
        <div class="card-body flex flex-col gap-4 sm:flex-row sm:items-end">
            {{ $fields ?? $slot }}

            <button type="submit" class="btn btn-secondary sm:shrink-0">
                <x-icon name="magnifying-glass" class="h-4 w-4" />
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>
