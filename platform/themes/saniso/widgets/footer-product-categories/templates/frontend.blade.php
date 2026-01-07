@php
    $layout = $config['layout'] ?? 'carousel';
    $showOnMobileOnly = $config['show_on_mobile_only'] ?? 'no';
    $title = $config['title'] ?? '';
    $subtitle = $config['subtitle'] ?? '';
    $displayType = $config['display_type'] ?? 'top_sale';
    
    // Auto-generate subtitle based on display type if not provided
    if (empty($subtitle)) {
        switch ($displayType) {
            case 'top_sale':
                $subtitle = __('Most Popular Categories by Sales');
                break;
            case 'top_product':
                $subtitle = __('Categories with Most Products');
                break;
            case 'new_added':
                $subtitle = __('Recently Added Categories');
                break;
            case 'custom':
                $subtitle = __('Handpicked Categories');
                break;
            default:
                $subtitle = __('Browse our categories');
                break;
        }
    }
    
    // Carousel settings - matching featured product categories exactly
    $slick = [
        'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
        'appendArrows' => '.arrows-wrapper',
        'arrows' => true,
        'dots' => false,
        'autoplay' => ($config['is_autoplay'] ?? 'yes') == 'yes',
        'infinite' => ($config['is_infinite'] ?? 'yes') == 'yes',
        'autoplaySpeed' => in_array($config['autoplay_speed'] ?? 3000, theme_get_autoplay_speed_options()) ? $config['autoplay_speed'] : 3000,
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
        ],
    ];
    
    // Grid settings for when layout is 'grid'
    $gridSettings = [
        'xl' => $config['columns_xl'] ?? 8,
        'lg' => $config['columns_lg'] ?? 6,  
        'md' => $config['columns_md'] ?? 4,
        'sm' => $config['columns_sm'] ?? 3,
        'xs' => $config['columns_xs'] ?? 2,
        'xxs' => $config['columns_xxs'] ?? 1,
    ];
    
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

@if (isset($categories) && $categories->isNotEmpty())
    <div class="widget-product-categories pb-2{{ $showOnMobileOnly == 'yes' ? ' d-lg-none' : '' }}">
        @if ($title || $subtitle)
            <div class="widget-header mb-2">
                @if ($title)
                    <h2 class="mb-0 py-2">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="mb-0">{{ $subtitle }}</p>
                @endif
            </div>
        @endif
        
        @if ($layout == 'carousel')
            {{-- Carousel Layout - Exact match to featured product categories --}}
            <div class="product-categories-body pb-4 arrows-top-right">
                <div
                    class="product-categories-box slick-slides-carousel"
                    data-slick="{{ json_encode($slick) }}"
                >
                    @foreach ($categories as $item)
                        <div class="product-category-item p-3">
                            <div class="category-item-body p-3">
                                <a class="d-block" href="{{ $item->url ?? route('public.products', ['categories' => $item->slug]) }}">
                                    <div class="category__thumb img-fluid-eq mb-0">
                                        <div class="img-fluid-eq__dummy"></div>
                                        <div class="img-fluid-eq__wrap">
                                            <img
                                                class="mx-auto"
                                                src="{{ RvMedia::getImageUrl($item->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                                alt="icon {{ $item->name }}"
                                                loading="lazy"
                                                decoding="async"
                                                width="100" 
                                                height="100"
                                            />
                                        </div>
                                    </div>
                                    <div class="category__text text-center">
                                        <span class="category__name d-block">{{ $item->name }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="arrows-wrapper"></div>
            </div>
        @else
            {{-- Grid Layout - Using the grid shortcode template style --}}
            <div class="product-categories-grid-body pb-4">
                <div class="row g-3 product-categories-grid">
                    @foreach ($categories as $item)
                        <div class="{{ $columnClass }}">
                            <div class="product-category-item h-100">
                                <div class="category-item-body h-100 d-flex flex-column">
                                    <a
                                        class="d-block h-100 d-flex flex-column"
                                        href="{{ $item->url ?? route('public.products', ['categories' => $item->slug]) }}"
                                    >
                                        <div class="category__thumb img-fluid-eq mb-0 flex-grow-1">
                                            <div class="img-fluid-eq__dummy"></div>
                                            <div class="img-fluid-eq__wrap">
                                                <img
                                                    class="mx-auto"
                                                    src="{{ RvMedia::getImageUrl($item->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                                    alt="icon {{ $item->name }}"
                                                    loading="lazy"
                                                    decoding="async"
                                                    width="100" 
                                                    height="100"
                                                />
                                            </div>
                                        </div>
                                        <div class="category__text text-center">
                                            <span class="category__name d-block">{{ $item->name }}</span>
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
@endif

<style>
    /* Proper image sizing */
    .widget-product-categories .category__thumb img {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        object-position: top;
        transition: none !important;
        transform: none !important;
        background: transparent;
    }
    
    /* Fix category name positioning - ensure names appear below thumbnails */
    .widget-product-categories .category-item-body {
        position: relative;
        z-index: 1;
        display: flex !important;
        flex-direction: column !important;
        height: 100%;
        border: none !important;
        border-radius: 8px;
        background: white;
        overflow: hidden;
    }
    
    .widget-product-categories .category__thumb {
        position: relative;
        z-index: 1;
        margin-bottom: 0 !important;
        flex-shrink: 0;
        overflow: hidden;
    }
    
    .widget-product-categories .category__text {
        position: relative;
        z-index: 2;
        background: white !important;
        padding: 12px 8px !important;
        margin-top: 0 !important;
        border: none !important;
        border-radius: 0;
    }
    
    .widget-product-categories .category__name {
        display: block !important;
        position: relative;
        z-index: 3;
        color: #333 !important;
        font-weight: 600;
        text-align: center;
        line-height: 1.3;
        font-size: 14px;
        background: transparent !important;
        border-radius: 0;
        padding: 0;
        margin: 0;
    }
    
    @media only screen and (min-width: 768px) {
        .category-section {
            padding-left: 15px;
            padding-right: 15px;
        }
    }

    @media (max-width: 767px) {
        /* Force exactly 2 slides visible with no partial slides */
        .widget-product-categories .slick-track {
            display: flex !important;
            width: 100% !important;
        }
        .widget-product-categories .slick-slide {
            width: 50% !important;
            float: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-sizing: border-box !important;
            flex: 0 0 50% !important;
        }
        .widget-product-categories .slick-list {
            overflow: hidden !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .widget-product-categories .slick-slider {
            box-sizing: border-box !important;
            overflow: hidden !important;
        }
        .widget-product-categories .slick-slide > div {
            width: 100% !important;
            padding: 0 !important;
        }
        /* Prevent any margin artifacts or partial slide visibility */
        .widget-product-categories .product-categories-box {
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .widget-product-categories .product-category-item {
            margin: 0 !important;
            padding: 8px !important;
            box-sizing: border-box !important;
        }
        /* Force consistent positioning regardless of slide index */
        .widget-product-categories .slick-track[style*="transform"] {
            transition: transform 0.3s ease !important;
        }
        /* Ensure no overflow or bleed */
        .widget-product-categories .product-categories-body {
            overflow: hidden !important;
        }
    }
</style>
