@php
    use Botble\Ecommerce\Facades\ProductCategoryHelper;
    use Botble\Ecommerce\Models\ProductCategory;

    // Get categories with proper language support - Enhanced debugging
    try {
        // First try: Use ProductCategoryHelper to get categories with proper language filtering
        $categoriesWithSales = ProductCategoryHelper::getProductCategoriesWithUrl([], ['is_featured' => true], 10);

        // Second try: If no featured categories found, get any published categories
        if ($categoriesWithSales->isEmpty()) {
            $categoriesWithSales = ProductCategoryHelper::getProductCategoriesWithUrl([], [], 10);
        }

        // Third try: Direct query with language support
        if ($categoriesWithSales->isEmpty()) {
            $categoriesWithSales = ProductCategory::query()
                ->where('status', 'published')
                ->where('parent_id', 0) // Only parent categories
                ->with(['slugable'])
                ->orderBy('order', 'ASC')
                ->orderBy('name', 'ASC')
                ->limit(10)
                ->get();
        }

        // Fourth try: Get any categories regardless of language
        if ($categoriesWithSales->isEmpty()) {
            $categoriesWithSales = ProductCategory::query()
                ->where('status', 'published')
                ->limit(10)
                ->get();
        }

        // Ensure URL attribute for each category
        $categoriesWithSales->each(function ($category) {
            if (!isset($category->url) || empty($category->url)) {
                $category->url = $category->slug ? route('public.products', ['categories' => $category->slug]) : route('public.products');
            }
            // Ensure name is not empty
            if (empty($category->name)) {
                $category->name = $category->slug ? ucfirst(str_replace('-', ' ', $category->slug)) : 'Category';
            }
        });

    } catch (Exception $e) {
        // Final fallback - just get any published categories
        $categoriesWithSales = ProductCategory::query()
            ->where('status', 'published')
            ->limit(10)
            ->get();
            
        // Add URL attribute for each category
        $categoriesWithSales->each(function ($category) {
            if (!isset($category->url) || empty($category->url)) {
                $category->url = $category->slug ? route('public.products', ['categories' => $category->slug]) : route('public.products');
            }
            // Ensure name is not empty
            if (empty($category->name)) {
                $category->name = $category->slug ? ucfirst(str_replace('-', ' ', $category->slug)) : 'Category';
            }
        });
    }

    // Carousel settings similar to featured product categories
    $slick = [
        'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
        'appendArrows' => '.arrows-wrapper',
        'arrows' => true,
        'dots' => false,
        'autoplay' => true,
        'infinite' => true,
        'autoplaySpeed' => 3000,
        'speed' => 800,
        'slidesToShow' => 8,
        'slidesToScroll' => 1,
        'responsive' => [
            [
                'breakpoint' => 1700,
                'settings' => [
                    'slidesToShow' => 7,
                ],
            ],
            [
                'breakpoint' => 1500,
                'settings' => [
                    'slidesToShow' => 6,
                ],
            ],
            [
                'breakpoint' => 1199,
                'settings' => [
                    'slidesToShow' => 5,
                ],
            ],
            [
                'breakpoint' => 1024,
                'settings' => [
                    'slidesToShow' => 4,
                ],
            ],
            [
                'breakpoint' => 767,
                'settings' => [
                    'arrows' => false,
                    'dots' => false,
                    'slidesToShow' => 2,
                    'slidesToScroll' => 1,
                    'centerMode' => false,
                    'variableWidth' => false,
                    'adaptiveHeight' => false,
                    'swipeToSlide' => true,
                    'waitForAnimate' => false,
                    'cssEase' => 'linear',
                    'edgeFriction' => 0,
                    'touchThreshold' => 10,
                    'useTransform' => true,
                    'useCSS' => true,
                ],
            ],
            [
                'breakpoint' => 575,
                'settings' => [
                    'arrows' => false,
                    'dots' => false,
                    'slidesToShow' => 2,
                    'slidesToScroll' => 1,
                    'centerMode' => false,
                    'variableWidth' => false,
                    'adaptiveHeight' => false,
                    'swipeToSlide' => true,
                    'waitForAnimate' => false,
                    'cssEase' => 'linear',
                    'edgeFriction' => 0,
                    'touchThreshold' => 10,
                    'useTransform' => true,
                    'useCSS' => true,
                ],
            ],
        ],
    ];
@endphp

@if ($categoriesWithSales->isNotEmpty())
    {{-- Debug information (remove in production) --}}
    @if(config('app.debug'))
        <div class="debug-info" style="background: #f8f9fa; padding: 10px; margin: 10px 0; font-size: 12px; border: 1px solid #dee2e6;">
            <strong>Debug Info:</strong> Found {{ $categoriesWithSales->count() }} categories |
            Current Page: {{ request()->route()->getName() ?? 'unknown' }} |
            Language: {{ app()->getLocale() }}
        </div>
    @endif

    <div class="footer-category-carousel py-3">
        <div class="container-xxxl">
            <div class="row">
                <div class="col-12">
                    <h3 class="text-center mb-3">{{ __('Shop by Category') }}</h3>
                    <div class="product-categories-body pb-3 arrows-top-right">
                        <div
                            class="product-categories-box slick-slides-carousel"
                            data-slick="{{ json_encode($slick) }}"
                        >
                            @foreach ($categoriesWithSales as $category)
                                <div class="product-category-item p-3">
                                    <div class="category-item-body p-3" style="background: #fff; border: 1px solid #e9ecef; border-radius: 8px; transition: all 0.3s ease; height: 100%; box-shadow: 0 2px 4px rgba(0,0,0,0.05);" 
                                         onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.1)'; this.style.borderColor='#007bff'; this.querySelector('.category__name').style.color='#ffd700'; this.querySelector('.category__overlay').style.background='linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.8) 100%)';" 
                                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)'; this.style.borderColor='#e9ecef'; this.querySelector('.category__name').style.color='#fff'; this.querySelector('.category__overlay').style.background='linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%)';"">
                                        <a class="d-block" href="{{ $category->url ?? route('public.products', ['categories' => $category->slug]) }}" style="text-decoration: none; color: inherit;">
                                            <div class="category__thumb img-fluid-eq mb-3 position-relative" style="position: relative; border-radius: 8px; overflow: hidden;">
                                                <div class="img-fluid-eq__dummy"></div>
                                                <div class="img-fluid-eq__wrap position-relative" style="position: relative; border-radius: 8px; overflow: hidden;">
                                                    @if($category->image)
                                                        <img
                                                            class="mx-auto"
                                                            src="{{ RvMedia::getImageUrl($category->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                                            alt="icon {{ $category->name ?? 'Category' }}"
                                                            loading="lazy"
                                                            decoding="async"
                                                            width="80" 
                                                            height="80"
                                                            style="border-radius: 8px;"
                                                        />
                                                    @else
                                                        <div class="category-placeholder d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6; margin: 0 auto;">
                                                            <i class="fa fa-folder" style="color: #6c757d; font-size: 24px;"></i>
                                                        </div>
                                                    @endif
                                                    <div class="category__overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%); z-index: 1;"></div>
                                                    <div class="category__text " style="position: absolute; bottom: 8px; left: 8px; right: 8px; z-index: 2 !important; text-align: center;">
                                                        <span class="category__name d-block" style="color: #fff; font-weight: 600; font-size: 12px; line-height: 1.2; text-shadow: 0 1px 3px rgba(0,0,0,0.8); background: rgba(0,0,0,0.6); padding: 4px 6px; border-radius: 4px; display: inline-block; word-wrap: break-word; max-width: 100%;">{{ $category->name ?? ($category->slug ? ucfirst(str_replace('-', ' ', $category->slug)) : 'Category') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="arrows-wrapper"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
/* Footer Category Carousel - Complete CSS for all pages */
.footer-category-carousel {
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.footer-category-carousel h3 {
    color: #333;
    font-weight: 600;
    margin-bottom: 1rem;
}

/* Product category items - Similar to featured categories */
.footer-category-carousel .product-category-item {
    margin: 0;
    padding: 8px;
}

.footer-category-carousel .category-item-body {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 12px;
    transition: all 0.3s ease;
    height: 100%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.footer-category-carousel .category-item-body:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.footer-category-carousel .category__thumb {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
}

.footer-category-carousel .img-fluid-eq__wrap {
    border-radius: 8px;
    overflow: hidden;
}

.footer-category-carousel .category__overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
    z-index: 2;
}

.footer-category-carousel .category__text {
    position: absolute;
    bottom: 8px;
    left: 8px;
    right: 8px;
    z-index: 2 !important;
    text-align: center;
}

.footer-category-carousel .category__name {
    color: #fff;
    font-weight: 600;
    font-size: 12px;
    line-height: 1.2;
    text-shadow: 0 1px 3px rgba(0,0,0,0.8);
    background: rgba(0,0,0,0.6);
    padding: 4px 6px;
    border-radius: 4px;
    display: inline-block;
    word-wrap: break-word;
    max-width: 100%;
    z-index: 3;
}

.footer-category-carousel .category-item-body:hover .category__name {
    color: #ffd700;
    background: rgba(0,0,0,0.8);
}

.footer-category-carousel .category-item-body:hover .category__overlay {
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.8) 100%);
}

/* Category placeholder styling */
.footer-category-carousel .category-placeholder {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.footer-category-carousel .category-placeholder i {
    color: #6c757d;
    font-size: 24px;
}

/* Add widget-product-categories compatibility */
.widget-product-categories .category__text {
    position: relative;
    z-index: 2;
    background: transparent;
    padding: 8px 4px;
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.widget-product-categories .category__name {
    display: block !important;
    position: relative;
    z-index: 3;
    color: #333 !important;
    font-weight: 600;
    text-align: center;
    line-height: 1.3;
    font-size: 13px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 4px;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

.widget-product-categories .category__thumb {
    position: relative;
    z-index: 1;
    margin-bottom: 10px;
    flex-shrink: 0;
}

.widget-product-categories .product-category-item a {
    display: flex;
    flex-direction: column;
    height: 100%;
    text-decoration: none;
}

/* Responsive layout switching with GPU acceleration */
.widget-product-categories.responsive-layout .carousel-layout {
    display: block !important;
    transform: translateZ(0); /* Enable GPU acceleration */
}

.widget-product-categories.responsive-layout .grid-layout {
    display: none !important;
    transform: translateZ(0); /* Enable GPU acceleration */
}

/* Override Bootstrap d-none class for responsive layout */
.widget-product-categories.responsive-layout .carousel-layout.d-none {
    display: block !important;
}

.widget-product-categories.responsive-layout .grid-layout.d-none {
    display: none !important;
}

/* Fix category name positioning - ensure names appear below thumbnails */
.widget-product-categories .category-item-body {
    position: relative;
    z-index: 1;
}

/* Ensure proper layout flow */
.widget-product-categories .category__thumb {
    flex-shrink: 0;
}

.widget-product-categories .category__text {
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Force text visibility override any theme CSS */
.footer-category-carousel * {
    color: inherit !important;
}

.footer-category-carousel .text-dark,
.footer-category-carousel .category__name {
    color: #333 !important;
}

/* Optimize image rendering like featured categories */
.footer-category-carousel .category__thumb img,
.widget-product-categories .category__thumb img {
    will-change: transform;
    backface-visibility: hidden;
}

/* Improve carousel performance */
.footer-category-carousel .slick-slides-carousel,
.widget-product-categories .slick-slides-carousel {
    will-change: transform;
}

/* Carousel styling */
.footer-category-carousel .slick-slider {
    margin: 0;
}

.footer-category-carousel .slick-slide {
    padding: 0 5px;
}

.footer-category-carousel .slick-dots {
    bottom: -30px;
}

.footer-category-carousel .slick-arrow {
    width: 40px;
    height: 40px;
    background: #007bff;
    border-radius: 50%;
    z-index: 2;
}

.footer-category-carousel .slick-arrow:before {
    color: white;
    font-size: 16px;
}

.footer-category-carousel .slick-prev {
    left: -20px;
}

.footer-category-carousel .slick-next {
    right: -20px;
}

    @media (max-width: 767px) {
        /* Force exactly 2 slides visible with no partial slides - fallback carousel */
        .footer-category-carousel .slick-track {
            display: flex !important;
            width: 100% !important;
        }
        .footer-category-carousel .slick-slide {
            width: 50% !important;
            float: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-sizing: border-box !important;
            flex: 0 0 50% !important;
        }
        .footer-category-carousel .slick-list {
            overflow: hidden !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .footer-category-carousel .slick-slider {
            box-sizing: border-box !important;
            overflow: hidden !important;
        }
        .footer-category-carousel .slick-slide > div {
            width: 100% !important;
            padding: 0 !important;
        }
        /* Prevent any margin artifacts or partial slide visibility */
        .footer-category-carousel .product-categories-box {
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .footer-category-carousel .product-category-item {
            margin: 0 !important;
            padding: 8px !important;
            box-sizing: border-box !important;
        }
        /* Force consistent positioning regardless of slide index */
        .footer-category-carousel .slick-track[style*="transform"] {
            transition: transform 0.3s ease !important;
        }
        /* Ensure no overflow or bleed */
        .footer-category-carousel .product-categories-body {
            overflow: hidden !important;
        }
        
        /* Mobile text improvements */
        .footer-category-carousel .category__name {
            font-size: 11px !important;
        }
        
        /* Widget product categories mobile responsive */
        .widget-product-categories.responsive-layout .carousel-layout,
        .widget-product-categories.responsive-layout .carousel-layout.d-none {
            display: none !important;
        }
        
        .widget-product-categories.responsive-layout .grid-layout,
        .widget-product-categories.responsive-layout .grid-layout.d-none {
            display: block !important;
        }
    }
</style>

<script>
(function() {
    'use strict';
    
    let resizeTimeout = null;
    let isInitialized = false;
    const RESIZE_DELAY = 100; // Reduced from 250ms for better responsiveness
    
    // Cache DOM elements for better performance
    const containerCache = new Map();
    
    function initializeContainers() {
        if (isInitialized) return;
        
        const containers = document.querySelectorAll('.widget-product-categories.responsive-layout');
        containers.forEach(function(container, index) {
            const breakpoint = parseInt(container.dataset.mobileBreakpoint) || 768;
            const carouselLayout = container.querySelector('.carousel-layout');
            const gridLayout = container.querySelector('.grid-layout');
            const slickCarousel = carouselLayout?.querySelector('.slick-slides-carousel');
            
            // Check if carousel is already initialized
            const isAlreadyActive = slickCarousel && $(slickCarousel).hasClass('slick-initialized');
            
            containerCache.set(index, {
                container,
                breakpoint,
                carouselLayout,
                gridLayout,
                slickCarousel,
                isCarouselActive: isAlreadyActive
            });
        });
        
        isInitialized = true;
    }
    
    function handleResponsiveLayout() {
        if (!isInitialized) return;
        
        const currentWidth = window.innerWidth;
        
        containerCache.forEach(function(cached) {
            try {
                const { breakpoint, carouselLayout, gridLayout, slickCarousel } = cached;
                const isMobile = currentWidth <= breakpoint;
                
                if (isMobile) {
                    // Mobile: show grid, hide carousel
                    if (carouselLayout && carouselLayout.style.display !== 'none') {
                        carouselLayout.style.display = 'none';
                    }
                    if (gridLayout && gridLayout.style.display !== 'block') {
                        gridLayout.style.display = 'block';
                    }
                    
                    // Destroy carousel if it exists and is active
                    if (slickCarousel && cached.isCarouselActive && typeof $ !== 'undefined' && $.fn.slick) {
                        try {
                            $(slickCarousel).slick('unslick');
                            cached.isCarouselActive = false;
                        } catch(e) {
                            // Carousel already destroyed or doesn't exist
                        }
                    }
                } else {
                    // Desktop/Tablet: show carousel, hide grid
                    if (carouselLayout && carouselLayout.style.display !== 'block') {
                        carouselLayout.style.display = 'block';
                    }
                    if (gridLayout && gridLayout.style.display !== 'none') {
                        gridLayout.style.display = 'none';
                    }
                    
                    // Initialize carousel if not already done
                    if (slickCarousel && !cached.isCarouselActive && typeof $ !== 'undefined' && $.fn.slick) {
                        try {
                            // Make sure carousel is not already initialized
                            if (!$(slickCarousel).hasClass('slick-initialized')) {
                                const slickData = slickCarousel.getAttribute('data-slick');
                                if (slickData) {
                                    const config = JSON.parse(slickData);
                                    $(slickCarousel).slick(config);
                                    cached.isCarouselActive = true;
                                }
                            } else {
                                cached.isCarouselActive = true;
                            }
                        } catch(e) {
                            console.warn('Failed to initialize carousel:', e);
                        }
                    }
                }
            } catch(e) {
                console.warn('Error in responsive layout handler:', e);
            }
        });
    }
    
    function throttledResize() {
        if (resizeTimeout) {
            clearTimeout(resizeTimeout);
        }
        resizeTimeout = setTimeout(handleResponsiveLayout, RESIZE_DELAY);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeContainers();
            handleResponsiveLayout();
        });
    } else {
        initializeContainers();
        handleResponsiveLayout();
    }
    
    // Handle window resize with throttling
    window.addEventListener('resize', throttledResize, { passive: true });
    
    // Cleanup on page unload to prevent memory leaks
    window.addEventListener('beforeunload', function() {
        if (resizeTimeout) {
            clearTimeout(resizeTimeout);
        }
        window.removeEventListener('resize', throttledResize);
        containerCache.clear();
    });
})();
</script>
