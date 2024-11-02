@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
    <div class="flex justify-between flex-1 sm:hidden">
        @if ($paginator->onFirstPage())
        <span class="px-4 py-2 text-gray-500 bg-gray-200 rounded cursor-not-allowed">
            {!! __('pagination.previous') !!}
        </span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
            {!! __('pagination.previous') !!}
        </a>
        @endif

        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="px-4 py-2 ml-3 text-white bg-blue-500 rounded hover:bg-blue-600">
            {!! __('pagination.next') !!}
        </a>
        @else
        <span class="px-4 py-2 ml-3 text-gray-500 bg-gray-200 rounded cursor-not-allowed">
            {!! __('pagination.next') !!}
        </span>
        @endif
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center">
        <div>
            <span class="relative z-0 inline-flex shadow-sm rounded-md">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span class="px-3 py-1 mr-2 text-gray-500 bg-gray-200 rounded cursor-not-allowed" aria-hidden="true">&laquo;</span>
                </span>
                @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-1 mr-2 text-white bg-blue-500 rounded hover:bg-blue-600" aria-label="{{ __('pagination.previous') }}">&laquo;</a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                <span aria-disabled="true">
                    <span class="px-3 py-1 text-gray-500 bg-gray-200 rounded cursor-not-allowed">{{ $element }}</span>
                </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <span aria-current="page">
                    <span class="px-3 py-1 text-white bg-blue-600 rounded">{{ $page }}</span>
                </span>
                @else
                <a href="{{ $url }}" class="px-3 py-1 text-blue-500 bg-white border border-gray-300 rounded hover:bg-gray-100" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                    {{ $page }}
                </a>
                @endif
                @endforeach
                @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-1 ml-2 text-white bg-blue-500 rounded hover:bg-blue-600" aria-label="{{ __('pagination.next') }}">&raquo;</a>
                @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span class="px-3 py-1 ml-2 text-gray-500 bg-gray-200 rounded cursor-not-allowed" aria-hidden="true">&raquo;</span>
                </span>
                @endif
            </span>
        </div>
    </div>
</nav>
@endif