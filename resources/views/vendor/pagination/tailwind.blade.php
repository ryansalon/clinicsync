@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between gap-3 pt-2">
        {{-- Mobile pagination controls --}}
        <div class="flex flex-1 justify-between gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed">
                    <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-secondary btn-sm">
                    <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                    Previous
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-secondary btn-sm">
                    Next
                    <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                </a>
            @else
                <span class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed">
                    Next
                    <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                </span>
            @endif
        </div>

        {{-- Desktop pagination controls --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-slate-500">
                    Showing
                    <span class="font-semibold text-slate-700">{{ $paginator->firstItem() }}</span>
                    to
                    <span class="font-semibold text-slate-700">{{ $paginator->lastItem() }}</span>
                    of
                    <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            <div class="flex items-center gap-1.5">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Previous page"
                          class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed px-2.5">
                        <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"
                       class="btn btn-secondary btn-sm px-2.5">
                        <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true" class="px-2 text-xs font-semibold text-slate-400">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-teal-600 bg-teal-600 px-2.5 text-xs font-semibold text-white shadow-xs">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-medium text-slate-700 shadow-xs transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500/60"
                                   aria-label="Go to page {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"
                       class="btn btn-secondary btn-sm px-2.5">
                        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Next page"
                          class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed px-2.5">
                        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
