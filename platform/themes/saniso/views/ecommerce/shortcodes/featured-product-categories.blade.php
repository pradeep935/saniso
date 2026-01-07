@php
    $layoutType = $shortcode->layout_type ?? 'responsive';
    $mobileBreakpoint = max(320, min(1200, (int)($shortcode->mobile_breakpoint ?? 768))); // Validate breakpoint
    $displayType = $shortcode->display_type ?? 'featured';
    
    // Get carousel slide settings from shortcode with validation
    $slidesXL = max(1, min(12, (int)($shortcode->carousel_slides_xl ?? 8)));
    $slidesLG = max(1, min(12, (int)($shortcode->carousel_slides_lg ?? 7)));
    $slidesMD = max(1, min(12, (int)($shortcode->carousel_slides_md ?? 6)));
    $slidesSM = max(1, min(12, (int)($shortcode->carousel_slides_sm ?? 5)));
    $slidesTablet = max(1, min(12, (int)($shortcode->carousel_slides_tablet ?? 4)));
    $slidesMobile = max(1, min(6, (int)($shortcode->carousel_slides_mobile ?? 2)));
    
    // Validate autoplay speed
    $autoplaySpeed = in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options()) 
        ? (int)$shortcode->autoplay_speed 
        : 3000;
    
    // Optimized carousel settings
    $slick = [
        'rtl' => BaseHelper::isRtlEnabled(),
        'arrows' => true, // Use Slick's default arrows
        'dots' => false,
        'autoplay' => ($shortcode->is_autoplay ?? 'yes') === 'yes',
        'infinite' => ($shortcode->infinite ?? $shortcode->is_infinite ?? 'yes') === 'yes',
        'autoplaySpeed' => $autoplaySpeed,
        'speed' => 800,
        'slidesToShow' => $slidesXL,
        'slidesToScroll' => 1,
        'lazyLoad' => 'ondemand', // Add lazy loading for better performance
        'responsive' => [
            [
                'breakpoint' => 1700,
                'settings' => [
                    'slidesToShow' => $slidesLG,
                ],
            ],
            [
                'breakpoint' => 1500,
                'settings' => [
                    'slidesToShow' => $slidesMD,
                ],
            ],
            [
                'breakpoint' => 1199,
                'settings' => [
                    'slidesToShow' => $slidesSM,
                ],
            ],
            [
                'breakpoint' => 1024,
                'settings' => [
                    'slidesToShow' => $slidesTablet,
                ],
            ],
            [
                'breakpoint' => 767,
                'settings' => [
                    'arrows' => false,
                    'dots' => false,
                    'slidesToShow' => $slidesMobile,
                    'slidesToScroll' => min($slidesMobile, 2),
                ],
            ],
        ],
    ];
    
    // Grid settings with validation
    $gridColumnsDesktop = max(1, min(12, (int)($shortcode->grid_columns_desktop ?? 6)));
    $gridColumnsTablet = max(1, min(12, (int)($shortcode->grid_columns_tablet ?? 4)));
    $gridColumnsMobile = max(1, min(6, (int)($shortcode->grid_columns_mobile ?? 2)));
    
    // Calculate grid classes more efficiently
    $gridColClasses = [
        'col-' . (12 / $gridColumnsMobile),
        'col-sm-' . (12 / $gridColumnsMobile),
        'col-md-' . (12 / $gridColumnsTablet),
        'col-lg-' . (12 / $gridColumnsTablet),
        'col-xl-' . (12 / $gridColumnsDesktop),
        'col-xxl-' . (12 / $gridColumnsDesktop),
    ];
    
    $gridColumnClass = implode(' ', $gridColClasses);
    
    // Container classes based on layout type
    $containerClasses = 'widget-product-categories pt-5 pb-2';
    if ($layoutType === 'responsive') {
        $containerClasses .= ' responsive-layout';
    }
    
    // Auto-generate subtitle based on display type if not provided - use caching
    $subtitle = $shortcode->subtitle;
    if (empty($subtitle)) {
        $subtitles = [
            'top_sale' => __('Most Popular Categories by Sales'),
            'top_product' => __('Categories with Most Products'),
            'new_added' => __('Recently Added Categories'),
            'custom' => __('Handpicked Categories'),
            'featured' => __('Featured Categories'),
        ];
        $subtitle = $subtitles[$displayType] ?? $subtitles['featured'];
    }
    
    // Validate categories collection
    if (!isset($categories) || !is_object($categories) || !method_exists($categories, 'isNotEmpty')) {
        $categories = collect();
    }
@endphp
@if ($categories->isNotEmpty())
    <div class="{{ $containerClasses }}" data-layout="{{ $layoutType }}" data-mobile-breakpoint="{{ $mobileBreakpoint }}">
        <div class="container-xxxl">
            <div class="row">
                <div class="col-12">
                    <div class="row align-items-center mb-2 widget-header">
                        <div class="col-auto">
                            <h2 class="mb-0 py-2">{{ $shortcode->title }}</h2>
                            @if ($subtitle)
                                <p class="mb-0">{{ $subtitle }}</p>
                            @endif
                        </div>
                    </div>
                    
                    @if ($layoutType !== 'grid_only')
                        {{-- Carousel Layout --}}
                        <div class="product-categories-body pb-4 arrows-top-right carousel-layout{{ $layoutType === 'responsive' ? ' d-none' : '' }}">
                            <div
                                class="product-categories-box slick-slides-carousel"
                                data-slick="{{ json_encode($slick) }}"
                            >
                                @foreach ($categories as $item)
                                    <div class="product-category-item p-3">
                                        <div class="category-item-body p-3">
                                            <a
                                                class="d-block"
                                                href="{{ $item->url ?? route('public.products', ['categories' => $item->slug]) }}"
                                            >                                    <div class="category__thumb img-fluid-eq mb-3">
                                        <div class="img-fluid-eq__dummy"></div>
                                        <div class="img-fluid-eq__wrap">
                                            <img
                                                class="mx-auto"
                                                src="{{ RvMedia::getImageUrl($item->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                                alt="icon {{ $item->name }}"
                                                loading="lazy"
                                                decoding="async"
                                                width="80" 
                                                height="80"
                                            />
                                        </div>
                                    </div>
                                                <div class="category__text text-center py-2 text-truncate">
                                                    <span class="category__name">{{ $item->name }}</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if ($layoutType !== 'carousel_only')
                        {{-- Grid Layout --}}
                        <div class="product-categories-grid-body pb-4 grid-layout{{ $layoutType === 'responsive' ? ' d-none' : '' }}">
                            <div class="row g-3 product-categories-grid">
                                @foreach ($categories as $item)
                                    <div class="{{ $gridColumnClass }}">
                                        <div class="product-category-item  h-100">
                                            <div class="category-item-body h-100 d-flex flex-column">
                                                <a
                                                    class="d-block h-100 d-flex flex-column"
                                                    href="{{ $item->url ?? route('public.products', ['categories' => $item->slug]) }}"
                                                >
                                                    <div class="category__thumb img-fluid-eq mb-3 flex-grow-1">
                                                        <div class="img-fluid-eq__dummy"></div>
                                                        <div class="img-fluid-eq__wrap">
                                                            <img
                                                                class="mx-auto"
                                                                src="{{ RvMedia::getImageUrl($item->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                                                alt="icon {{ $item->name }}"
                                                                loading="lazy"
                                                                decoding="async"
                                                                width="80" 
                                                                height="80"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="category__text text-center py-2 text-truncate">
                                                        <span class="category__name">{{ $item->name }}</span>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @if ($layoutType === 'responsive')
        <style>
        /* Responsive layout switching with GPU acceleration */
        .widget-product-categories.responsive-layout .carousel-layout {
            display: block !important;
            transform: translateZ(0); /* Enable GPU acceleration */
        }
        
        .widget-product-categories.responsive-layout .grid-layout {
            display: none !important;
            transform: translateZ(0); /* Enable GPU acceleration */
        }
        
        /* Optimize image rendering */
        .category__thumb img {
            will-change: transform;
            backface-visibility: hidden;
        }
        
        /* Improve carousel performance */
        .slick-slides-carousel {
            will-change: transform;
        }
        
        @media (max-width: {{ $mobileBreakpoint }}px) {
            .widget-product-categories.responsive-layout .carousel-layout {
                display: none !important;
            }
            
            .widget-product-categories.responsive-layout .grid-layout {
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
    @endif
@endif
