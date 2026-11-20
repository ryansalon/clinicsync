@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between gap-3 pt-2">
        {{-- Mobile pagination controls --}}
        <div class="flex flex-1 justify-between gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm cursor-not-allowed opacity-50">
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
                <span class="btn btn-secondary btn-sm cursor-not-allowed opacity-50">
                    Next
                    <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                </span>
            @endif
        </div>

        {{-- Desktop pagination controls --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="cs-num text-xs text-meta">
                    Showing
                    <span class="font-semibold text-bright">{{ $paginator->firstItem() }}</span>
                    to
                    <span class="font-semibold text-bright">{{ $paginator->lastItem() }}</span>
                    of
                    <span class="font-semibold text-bright">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            <div class="flex items-center gap-1.5">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Previous page"
                          class="btn btn-secondary btn-sm cursor-not-allowed px-2.5 opacity-50">
                        <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"
                       class="btn btn-secondary btn-sm px-2.5">
                        <x-icon name="chevron-left" class="h-3.5 w-3.5" />
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="px-2 text-xs font-semibold text-meta">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="cs-num inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-brand bg-brand px-2.5 font-mono text-xs font-semibold text-white">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="cs-num inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-hairline bg-surface px-2.5 font-mono text-xs font-medium text-bright transition-[transform,background-color,border-color,box-shadow] duration-150 ease-out hover:-translate-y-0.5 hover:border-brand/50 hover:bg-surface-subtle hover:shadow-[0_3px_8px_-3px_rgba(24,32,29,0.12)] active:translate-y-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"
                                   aria-label="Go to page {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"
                       class="btn btn-secondary btn-sm px-2.5">
                        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Next page"
                          class="btn btn-secondary btn-sm cursor-not-allowed px-2.5 opacity-50">
                        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
