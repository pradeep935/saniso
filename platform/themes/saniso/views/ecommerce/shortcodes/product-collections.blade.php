<div class="widget-products-with-category py-5 bg-light">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-0 py-2">{{ $shortcode->title }}</h2>
                </div>
                <div class="product-deals-day__body arrows-top-right">
                    <div
                        class="product-deals-day-body slick-slides-carousel"
                        data-slick="{{ json_encode([
                            'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
                            'appendArrows' => '.arrows-wrapper',
                            'arrows' => true,
                            'dots' => false,
                            'autoplay' => $shortcode->is_autoplay == 'yes',
                            'infinite' => $shortcode->infinite == 'yes' || $shortcode->is_infinite == 'yes',
                            'autoplaySpeed' => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options())
                                ? $shortcode->autoplay_speed
                                : 3000,
                            'speed' => 800,
                            'slidesToShow' => 5,
                            'slidesToScroll' => 1,
                            'swipeToSlide' => true,
                            'responsive' => [
                                [
                                    'breakpoint' => 1400,
                                    'settings' => [
                                        'slidesToShow' => 4,
                                    ],
                                ],
                                [
                                    'breakpoint' => 1199,
                                    'settings' => [
                                        'slidesToShow' => 3,
                                    ],
                                ],
                                [
                                    'breakpoint' => 1024,
                                    'settings' => [
                                        'slidesToShow' => 2,
                                    ],
                                ],
                                [
                                    'breakpoint' => 767,
                                    'settings' => [
                                        'arrows' => true,
                                        'dots' => false,
                                        'slidesToShow' => 1.25, // match featured products
                                        'slidesToScroll' => 1,
                                        'centerMode' => false,
                                    ],
                                ],
                            ],
                        ]) }}"
                    >
                        @foreach ($products as $product)
                            <div class="product-inner">
                                {!! Theme::partial('ecommerce.product-item-featured', compact('product', 'wishlistIds', 'showTitle', 'showRating', 'showPrice', 'showDescription', 'showStoreInfo', 'showLabels', 'showAddToCart', 'showWishlist')) !!}
                            </div>
                        @endforeach
                    </div>
                    <div class="arrows-wrapper"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-inner{
    
}
.product-deals-day-body .slick-track {
    display: flex !important;
    align-items: stretch;
}
.product-deals-day-body .slick-slide {
    height: auto;
    display: flex !important;
    flex-direction: column;
}
.product-deals-day-body .product-inner {
    display: flex;
    flex-direction: column;
    flex: 1 1 0;
    margin: 0 8px;
    border-radius: 0.4rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.08), 0 1.5px 4px rgba(0,0,0,0.04);
    transition: box-shadow 0.2s, border-color 0.2s;
    margin-top:4px;
    margin-bottom:4px;
}
.product-deals-day-body .product-inner:hover {
    box-shadow: 0 6px 32px rgba(0,0,0,0.13);
}
.product-deals-day-body .product-inner .product-button .add-to-cart-button {
    width: 70%;
}
.product-deals-day-body .product-inner .product-button {
    flex-wrap: nowrap;
}
.product-inner .product-bottom-box {
    border: 0px solid #c9c9c9; 
    background-color: #ffffff ;
    margin: 0px 1px;
    border-radius: 0.4rem;
    transition: box-shadow 0.2s, border-color 0.2s;
}
.slick-slides-carousel .product-inner{
    border: none;
}

/* Mobile font sizes for widget header */
@media (max-width: 767px) {
    .widget-header h2 {
        font-size: 1rem !important;
    }
    
    .widget-header p {
        font-size: 11px !important;
    }
}
</style>
