@push('header')
<style>
/* Mobile-Responsive Pagination Styles */
.simple-pagination {
    gap: 0.5rem;
}

.simple-pagination .pagination {
    gap: 2px;
    margin: 0;
}

.simple-pagination .page-link {
    border-radius: 4px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
    font-weight: 500;
}

.simple-pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white !important;
    font-weight: bold;
}

.simple-pagination .page-item.disabled .page-link {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
}

.simple-pagination .page-link:hover:not(.disabled) {
    background-color: #007bff;
    border-color: #007bff;
    color: white !important;
    transform: translateY(-1px);
}

/* Mobile-specific styles */
@media (max-width: 575.98px) {
    .simple-pagination {
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }
    
    .simple-pagination .pagination {
        justify-content: center;
        flex-wrap: wrap;
        gap: 1px;
    }
    
    .simple-pagination .page-link {
        min-width: 32px !important;
        min-height: 32px !important;
        font-size: 13px !important;
        padding: 0.25rem;
    }
    
    /* Compact pagination on very small screens */
    @media (max-width: 400px) {
        .simple-pagination .page-link {
            min-width: 28px !important;
            min-height: 28px !important;
            font-size: 12px !important;
        }
        
        .simple-pagination .pagination {
            gap: 1px;
        }
    }
}

/* Tablet styles */
@media (min-width: 576px) and (max-width: 991.98px) {
    .simple-pagination .page-link {
        min-width: 38px !important;
        min-height: 38px !important;
        font-size: 14px !important;
    }
}

/* Desktop styles */
@media (min-width: 992px) {
    .simple-pagination .page-link {
        min-width: 40px !important;
        min-height: 40px !important;
        font-size: 15px !important;
    }
}

/* Touch-friendly hover states for mobile */
@media (hover: none) and (pointer: coarse) {
    .simple-pagination .page-link:hover {
        transform: none;
    }
    
    .simple-pagination .page-link:active {
        transform: scale(0.95);
        background-color: #007bff;
        color: white;
    }
}

/* Accessibility improvements */
.simple-pagination .page-link:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: none;
}

.simple-pagination .page-item.disabled .page-link {
    pointer-events: none;
    cursor: default;
}
</style>
@endpush

@if ($paginator->hasPages())
<div class="simple-pagination d-flex align-items-center flex-wrap">
    <p class="m-0 text-secondary d-none d-sm-block">
        Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </p>

    <ul class="m-0 pagination ms-auto d-flex flex-wrap justify-content-center justify-content-sm-end">
        {{-- First Page Link --}}
        @if ($paginator->currentPage() > 2)
            <li class="page-item">
                <a
                    class="page-link d-flex align-items-center justify-content-center"
                    href="{{ $paginator->url(1) }}"
                    rel="first"
                    style="min-width: 35px; min-height: 35px; font-size: 14px;"
                    title="First Page"
                    onmouseover="this.style.color='white'"
                    onmouseout="this.style.color=''"
                >
                    <span class="d-none d-sm-inline">««</span>
                    <span class="d-sm-none">‹‹</span>
                </a>
            </li>
        @endif

        {{-- Previous Page Link --}}
        <li @class(['page-item', 'disabled' => $paginator->onFirstPage()])>
            @if ($paginator->onFirstPage())
                <span
                    class="page-link text-muted d-flex align-items-center justify-content-center"
                    aria-disabled="true"
                    style="min-width: 35px; min-height: 35px; font-size: 14px;"
                >
                    <span>‹</span>
                </span>
            @else
                <a
                    class="page-link d-flex align-items-center justify-content-center"
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    style="min-width: 35px; min-height: 35px; font-size: 14px;"
                    title="Previous Page"
                    onmouseover="this.style.color='white'"
                    onmouseout="this.style.color=''"
                >
                    <span>‹</span>
                </a>
            @endif
        </li>

        {{-- Pagination Elements --}}
        @php
            $start = max(1, $paginator->currentPage() - 2);
            $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
            
            // Ensure we show at least 5 pages when possible
            if ($end - $start < 4) {
                if ($start == 1) {
                    $end = min($paginator->lastPage(), $start + 4);
                } else {
                    $start = max(1, $end - 4);
                }
            }
        @endphp

        {{-- Show first page and dots if needed --}}
        @if ($start > 1)
            <li class="page-item">
                <a class="page-link d-flex align-items-center justify-content-center" 
                   href="{{ $paginator->url(1) }}" 
                   style="min-width: 35px; min-height: 35px; font-size: 14px;"
                   onmouseover="this.style.color='white'" 
                   onmouseout="this.style.color=''">1</a>
            </li>
            @if ($start > 2)
                <li class="page-item disabled">
                    <span class="page-link d-flex align-items-center justify-content-center" 
                          style="min-width: 35px; min-height: 35px; font-size: 14px;">...</span>
                </li>
            @endif
        @endif

        {{-- Page Numbers --}}
        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $paginator->currentPage())
                <li class="page-item active">
                    <span class="page-link text-white d-flex align-items-center justify-content-center" 
                          style="min-width: 35px; min-height: 35px; font-size: 14px; font-weight: bold;">{{ $page }}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link d-flex align-items-center justify-content-center" 
                       href="{{ $paginator->url($page) }}" 
                       style="min-width: 35px; min-height: 35px; font-size: 14px;"
                       onmouseover="this.style.color='white'" 
                       onmouseout="this.style.color=''">{{ $page }}</a>
                </li>
            @endif
        @endfor

        {{-- Show dots and last page if needed --}}
        @if ($end < $paginator->lastPage())
            @if ($end < $paginator->lastPage() - 1)
                <li class="page-item disabled">
                    <span class="page-link d-flex align-items-center justify-content-center" 
                          style="min-width: 35px; min-height: 35px; font-size: 14px;">...</span>
                </li>
            @endif
            <li class="page-item">
                <a class="page-link d-flex align-items-center justify-content-center" 
                   href="{{ $paginator->url($paginator->lastPage()) }}" 
                   style="min-width: 35px; min-height: 35px; font-size: 14px;"
                   onmouseover="this.style.color='white'" 
                   onmouseout="this.style.color=''">{{ $paginator->lastPage() }}</a>
            </li>
        @endif

        {{-- Next Page Link --}}
        <li @class(['page-item', 'disabled' => !$paginator->hasMorePages()])>
            @if ($paginator->hasMorePages())
                <a
                    class="page-link d-flex align-items-center justify-content-center"
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    style="min-width: 35px; min-height: 35px; font-size: 14px;"
                    title="Next Page"
                    onmouseover="this.style.color='white'"
                    onmouseout="this.style.color=''"
                >
                    <span>›</span>
                </a>
            @else
                <span
                    class="page-link text-muted d-flex align-items-center justify-content-center"
                    aria-disabled="true"
                    style="min-width: 35px; min-height: 35px; font-size: 14px;"
                >
                    <span>›</span>
                </span>
            @endif
        </li>

        {{-- Last Page Link --}}
        @if ($paginator->currentPage() < $paginator->lastPage() - 1)
            <li class="page-item">
                <a
                    class="page-link d-flex align-items-center justify-content-center"
                    href="{{ $paginator->url($paginator->lastPage()) }}"
                    rel="last"
                    style="min-width: 35px; min-height: 35px; font-size: 14px;"
                    title="Last Page"
                    onmouseover="this.style.color='white'"
                    onmouseout="this.style.color=''"
                >
                    <span class="d-none d-sm-inline">»»</span>
                    <span class="d-sm-none">››</span>
                </a>
            </li>
        @endif
    </ul>
</div>

{{-- Mobile-specific pagination info --}}
<div class="d-block d-sm-none text-center mt-2">
    <small class="text-muted">
        Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }} 
        ({{ $paginator->total() }} items)
    </small>
</div>
@endif
