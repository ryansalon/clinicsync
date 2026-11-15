@props([
    /** @var array<int, array{label: string, href?: string}> $items */
    'items' => [],
])

{{-- Trail back to the parent pages, e.g.
     <x-breadcrumb :items="[['label' => 'Today’s queue', 'href' => route('physician.dashboard')], ['label' => 'Patient record']]" /> --}}
<nav aria-label="Breadcrumb" class="mb-2">
    <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs">
        @foreach ($items as $index => $item)
            <li class="flex items-center gap-1.5">
                @if ($index > 0)
                    <x-icon name="chevron-right" class="h-3.5 w-3.5 text-[#465854]" />
                @endif

                @if (! empty($item['href']) && ! $loop->last)
                    <a href="{{ $item['href'] }}"
                       class="font-medium text-[#465854] transition hover:text-[#0F766E] focus:outline-none focus-visible:underline">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-semibold text-[#14201E]" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
