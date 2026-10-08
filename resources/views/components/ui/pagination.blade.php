{{-- <x-ui.pagination :paginator="$posts" /> — RTL-aware, accessible pagination. --}}
@props (['paginator'])

@if ($paginator->hasPages())
    <nav
        aria-label="{{ __('ui.pagination.label') }}"
        {{
            $attributes->class(
                'mt-6 flex items-center justify-between gap-4',
            )
        }}
    >
        <p class="text-sm text-fg-muted">
            {{
                __('ui.pagination.showing', [
                    'first' => $paginator->firstItem(),
                    'last' => $paginator->lastItem(),
                    'total' => $paginator->total(),
                ])
            }}
        </p>

        <ul class="flex items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span
                        class="inline-flex h-9 items-center rounded-control px-3 text-sm text-fg-muted opacity-50"
                        aria-disabled="true"
                    >
                        {{ __('ui.pagination.previous') }}
                    </span>
                @else
                    <a
                        href="{{ $paginator->previousPageUrl() }}"
                        rel="prev"
                        class="inline-flex h-9 items-center rounded-control px-3 text-sm hover:bg-surface-muted"
                    >
                        {{ __('ui.pagination.previous') }}
                    </a>
                @endif
            </li>

            @foreach ($paginator->getUrlRange(
                    max(1, $paginator->currentPage() - 2),
                    min($paginator->lastPage(), $paginator->currentPage() + 2)
                )
                as $page => $url)
                <li>
                    @if ($page === $paginator->currentPage())
                        <span
                            aria-current="page"
                            class="inline-flex size-9 items-center justify-center rounded-control bg-primary text-sm text-primary-fg"
                        >
                            {{ $page }}
                        </span>
                    @else
                        <a
                            href="{{ $url }}"
                            aria-label="{{ __('ui.pagination.page', ['page' => $page]) }}"
                            class="inline-flex size-9 items-center justify-center rounded-control text-sm hover:bg-surface-muted"
                        >
                            {{ $page }}
                        </a>
                    @endif
                </li>
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a
                        href="{{ $paginator->nextPageUrl() }}"
                        rel="next"
                        class="inline-flex h-9 items-center rounded-control px-3 text-sm hover:bg-surface-muted"
                    >
                        {{ __('ui.pagination.next') }}
                    </a>
                @else
                    <span
                        class="inline-flex h-9 items-center rounded-control px-3 text-sm text-fg-muted opacity-50"
                        aria-disabled="true"
                    >
                        {{ __('ui.pagination.next') }}
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
