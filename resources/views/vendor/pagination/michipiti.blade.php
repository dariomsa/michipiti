@if ($paginator->hasPages())
    <nav class="ec-pagination-nav" role="navigation" aria-label="Paginación">
        <div class="ec-pagination-shell">
            <div class="ec-pagination-summary">
                Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
            </div>

            <ul class="ec-pagination-list">
                @if ($paginator->onFirstPage())
                    <li class="ec-pagination-item is-disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span class="ec-pagination-link is-arrow" aria-hidden="true">
                            <i class="bi bi-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="ec-pagination-item">
                        <a class="ec-pagination-link is-arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="ec-pagination-item is-disabled" aria-disabled="true">
                            <span class="ec-pagination-link is-gap">{{ $element }}</span>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="ec-pagination-item is-active" aria-current="page">
                                    <span class="ec-pagination-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="ec-pagination-item">
                                    <a class="ec-pagination-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <li class="ec-pagination-item">
                        <a class="ec-pagination-link is-arrow" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="ec-pagination-item is-disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span class="ec-pagination-link is-arrow" aria-hidden="true">
                            <i class="bi bi-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
