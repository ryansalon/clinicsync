@props([
    /** @var array<int, array{label: string, href?: string}> $items */
    'items' => [],
])

<nav aria-label="Breadcrumb" class="mb-2">
    <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs">
        @foreach ($items as $index => $item)
            <li class="flex items-center gap-1.5">
                @if ($index > 0)
                    <x-icon name="chevron-right" class="h-3.5 w-3.5 text-meta" />
                @endif

                @if (! empty($item['href']) && ! $loop->last)
                    <a href="{{ $item['href'] }}"
                       class="font-medium text-meta transition hover:text-brand focus:outline-none focus-visible:underline">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-semibold text-bright" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
