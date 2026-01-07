@php
    // Define responsive grid settings similar to the carousel breakpoints
    $gridSettings = [
        'xl' => $shortcode->columns_xl ?? 8,  // 1700+ px
        'lg' => $shortcode->columns_lg ?? 6,  // 1200-1699 px  
        'md' => $shortcode->columns_md ?? 4,  // 992-1199 px
        'sm' => $shortcode->columns_sm ?? 3,  // 768-991 px
        'xs' => $shortcode->columns_xs ?? 2,  // 576-767 px
        'xxs' => $shortcode->columns_xxs ?? 1, // <576 px
    ];
    
    // Calculate Bootstrap column classes based on grid settings
    $colClasses = [
        'col-' . (12 / $gridSettings['xxs']),
        'col-sm-' . (12 / $gridSettings['xs']),
        'col-md-' . (12 / $gridSettings['sm']),
        'col-lg-' . (12 / $gridSettings['md']),
        'col-xl-' . (12 / $gridSettings['lg']),
        'col-xxl-' . (12 / $gridSettings['xl']),
    ];
    
    $columnClass = implode(' ', $colClasses);
@endphp

@if ($categories->isNotEmpty())
    <div class="widget-product-categories-grid pt-5 pb-2">
        <div class="container-xxxl">
            <div class="row">
                <div class="col-12">
                    <div class="row align-items-center mb-4 widget-header">
                        <div class="col-auto">
                            <h2 class="mb-0 py-2">{{ $shortcode->title }}</h2>
                            @if ($shortcode->subtitle)
                                <p class="mb-0">{{ $shortcode->subtitle }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="product-categories-grid-body pb-4">
                        <div class="row g-3 product-categories-grid">
                            @foreach ($categories as $item)
                                <div class="{{ $columnClass }}">
                                    <div class="product-category-item h-100">
                                        <div class="category-item-body p-3 h-100 d-flex flex-column">
                                            <a
                                                class="d-block h-100 d-flex flex-column"
                                                href="{{ route('public.single', $item->url) }}"
                                            >
                                                <div class="category__thumb img-fluid-eq mb-3 flex-grow-1">
                                                    <div class="img-fluid-eq__dummy"></div>
                                                    <div class="img-fluid-eq__wrap">
                                                        <img
                                                            class="mx-auto"
                                                            src="{{ RvMedia::getImageUrl($item->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                                            alt="icon {{ $item->name }}"
                                                            loading="lazy"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="category__text text-center py-2">
                                                    <span class="category__name d-block">{{ $item->name }}</span>
                                                    @if ($shortcode->show_product_count == 'yes' && isset($item->products_count))
                                                        <small class="text-muted">{{ $item->products_count }} {{ __('products') }}</small>
                                                    @endif
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if ($shortcode->show_view_all == 'yes' && $shortcode->view_all_url)
                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <a href="{{ $shortcode->view_all_url }}" class="btn btn-primary">
                                        {{ $shortcode->view_all_text ?: __('View All Categories') }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
.widget-product-categories-grid .product-category-item {
    border: 1px solid #f0f0f0;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: #fff;
    overflow: hidden;
}

.widget-product-categories-grid .product-category-item:hover {
    border-color: var(--primary-color, #007bff);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.widget-product-categories-grid .category-item-body {
    min-height: 180px;
}

.widget-product-categories-grid .category__thumb {
    position: relative;
    max-height: 120px;
}

.widget-product-categories-grid .img-fluid-eq {
    position: relative;
    width: 100%;
}

.widget-product-categories-grid .img-fluid-eq__dummy {
    padding-bottom: 75%; /* 4:3 aspect ratio */
}

.widget-product-categories-grid .img-fluid-eq__wrap {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.widget-product-categories-grid .img-fluid-eq__wrap img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.widget-product-categories-grid .category__name {
    font-weight: 500;
    color: var(--text-color, #333);
    font-size: 14px;
    line-height: 1.4;
}

.widget-product-categories-grid .product-category-item:hover .category__name {
    color: var(--primary-color, #007bff);
}

@media (max-width: 575.98px) {
    .widget-product-categories-grid .category-item-body {
        min-height: 150px;
        padding: 1rem !important;
    }
    
    .widget-product-categories-grid .category__thumb {
        max-height: 100px;
    }
    
    .widget-product-categories-grid .category__name {
        font-size: 13px;
    }
}

@media (max-width: 767.98px) {
    .widget-product-categories-grid .row.g-3 {
        --bs-gutter-x: 0.75rem;
        --bs-gutter-y: 0.75rem;
    }
}
</style>
