@if (isset($paginator) && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <div class="m-3">
        <nav>
            <ul class="pagination mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link waves-effect" aria-hidden="true">‹</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link waves-effect"
                            href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @php
                    $elements = isset($elements) ? $elements : ($paginator->links()->elements ?? []);
                @endphp
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link waves-effect">{{ $element }}</span>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link waves-effect">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link waves-effect"
                                        href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link waves-effect"
                            href="{{ $paginator->nextPageUrl() }}" rel="next">›</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link waves-effect" aria-hidden="true">›</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif
