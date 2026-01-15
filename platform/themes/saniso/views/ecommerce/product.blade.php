@php
    Theme::layout('full-width');
    Theme::set('bodyClass', 'single-product');
    
    // Define quote mode for the entire page
    use Botble\Ecommerce\Models\QuoteSettings;
    $isQuoteMode = QuoteSettings::shouldShowQuoteForm($product);
@endphp

@php
    $groupData = $product->groupOptions();
@endphp
{!! Theme::partial('page-header', ['size' => 'xxxl']) !!}

<div class="product-detail-container">
    <div class="bg-light py-md-5 px-lg-3 px-2">
        <div class="container-xxxl rounded-7 bg-white py-lg-5 py-md-4 py-3 px-3 px-md-4 px-lg-5">
            <div class="row">
                <div class="col-lg-5 col-md-12 mb-md-5 pb-md-5 mb-3">
                    @include(EcommerceHelper::viewPath('includes.product-gallery'))
                </div>
                <div class="col-lg-4 col-md-8 ps-4 product-details-content">
                    <div class="product-details js-product-content">
                        <div class="entry-product-header">
                            <div class="product-header-left">
                                <h1 class="fs-5 fw-normal product_title entry-title">{{ $product->name }}</h1>
                                <div class="product-entry-meta">
                                    @if ($product->brand->name)
                                        <p class="mb-0 me-2 pe-2 text-secondary">{{ __('Brand') }}: <a
                                                href="{{ $product->brand->url }}"
                                            >{{ $product->brand->name }}</a></p>
                                    @endif

                                    @if (EcommerceHelper::isReviewEnabled())
                                        <a
                                            class="anchor-link"
                                            href="#product-reviews-tab"
                                        >
                                            {!! Theme::partial('star-rating', ['avg' => $product->reviews_avg, 'count' => $product->reviews_count]) !!}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if (!$isQuoteMode)
                            {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
                        @endif

                        @if (is_plugin_active('marketplace') && $product->store_id)
                            <div class="product-meta-sold-by my-2">
                                <span class="d-inline-block me-1">{{ __('Sold By') }}: </span>
                                <a href="{{ $product->store->url }}">
                                    {{ $product->store->name }}
                                </a>
                            </div>
                        @endif

                        <div class="ps-list--dot">
                            {!! apply_filters('ecommerce_before_product_description', null, $product) !!}
                            {!! BaseHelper::clean($product->description) !!}
                            {!! apply_filters('ecommerce_after_product_description', null, $product) !!}
                        </div>

                        <div class="product-variations mb-4">

                            @foreach($groupData['options'] as $setName => $attributes)
                                <div class="variation-group mb-3">
                                    <label class="form-label fw-semibold">{{ ucfirst($setName) }}</label>
                                    
                                    <div class="d-flex flex-wrap gap-2 
                                                {{ $setName == 'color' ? 'color-swatches' : 'size-pills' }}">
                                        
                                        @foreach($attributes as $id => $attr)
                                            <a href="{{ $attr['url'] ?? 'javascript:void(0)' }}"
                                               class="variation-btn {{ $setName }}-btn"
                                               data-attr="{{ $setName }}"
                                               data-id="{{ $id }}"
                                               @if($setName == 'color')
                                                   style="background-color: {{ $attr['color'] ?? '#000' }}"
                                               @endif
                                               title="{{ $attr['label'] }}">
                                                @if($setName != 'color')
                                                    {{ $attr['label'] }}
                                                @endif
                                            </a>
                                        @endforeach

                                    </div>
                                </div>
                            @endforeach

                        </div>


                        {!! Theme::partial('ecommerce.product-availability', compact('product', 'productVariation')) !!}
                        @if (Botble\Ecommerce\Facades\FlashSale::isEnabled() && ($flashSale = $product->latestFlashSales()->first()))
                            <div class="deal-expire-date p-4 bg-light mb-2">
                                <div class="row">
                                    <div class="col-xxl-5 d-md-flex justify-content-center align-items-center">
                                        <div class="deal-expire-text mb-2">
                                            <div class="fw-bold text-uppercase">{{ __('Hurry up! Sale end in') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-7">
                                        <div class="countdown-wrapper d-none">
                                            <div
                                                class="expire-countdown col-auto"
                                                data-expire="{{ Carbon\Carbon::now()->diffInSeconds($flashSale->end_date) }}"
                                            >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center my-3">
                                    <div class="deal-sold row mt-2">
                                        @if (Botble\Ecommerce\Facades\FlashSale::isShowSaleCountLeft())
                                            <div class="deal-text col-auto">
                                                <span class="sold fw-bold">
                                                    <span class="text">{{ __('Sold') }}: </span>
                                                    <span class="value">{{ $flashSale->sale_count_left_label }}</span>
                                                </span>
                                            </div>
                                        @endif
                                        <div class="deal-progress col">
                                            <div class="progress">
                                                <div
                                                    class="progress-bar"
                                                    role="progressbar"
                                                    aria-valuenow="{{ $flashSale->sale_count_left_percent }}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                    style="width: {{ $flashSale->sale_count_left_percent }}%;"
                                                >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if ($isQuoteMode)
                            {{-- Quote Request Section --}}
                            {!! Theme::partial('ecommerce.product-request-quote', compact('product')) !!}
                        @else
                            {{-- Standard Cart Form --}}
                            {!! Theme::partial(
                                'ecommerce.product-cart-form',
                                compact('product', 'selectedAttrs', 'productVariation') + [
                                    'withButtons' => true,
                                    'withVariations' => true,
                                    'withProductOptions' => true,
                                    'wishlistIds' => \Theme\Farmart\Supports\Wishlist::getWishlistIds([$product->id]),
                                    'withBuyNow' => true,
                                ],
                            ) !!}
                        @endif
                        
                        <div class="meta-sku @if (!$product->sku) d-none @endif">
                            <span class="meta-label d-inline-block me-1">{{ __('SKU') }}:</span>
                            <span class="meta-value" data-bb-value="product-sku">{{ $product->sku }}</span>
                        </div>
                        @if ($product->categories->isNotEmpty())
                            <div class="meta-categories">
                                <span class="meta-label d-inline-block me-1">{{ __('Categories') }}: </span>
                                @foreach ($product->categories as $category)
                                    <a href="{{ $category->url }}">{{ $category->name }}</a>@if (!$loop->last),@endif
                                @endforeach
                            </div>
                        @endif
                        @if ($product->tags->isNotEmpty())
                            <div class="meta-categories">
                                <span class="meta-label d-inline-block me-1">{{ __('Tags') }}: </span>
                                @foreach ($product->tags as $tag)
                                    <a href="{{ $tag->url }}">{{ $tag->name }}</a>@if (!$loop->last),@endif
                                @endforeach
                            </div>
                        @endif
                        @if (theme_option('social_share_enabled', 'yes') == 'yes')
                            <div class="my-5">
                                {!! Theme::partial('share-socials', compact('product')) !!}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    {!! dynamic_sidebar('product_detail_sidebar') !!}
                </div>
            </div>
        </div>
    </div>
    <div class="container-xxxl">
        <div class="row product-detail-tabs mt-3 mb-4">
            <div class="col-md-3">
                <div
                    class="nav flex-column nav-pills me-3"
                    id="product-detail-tabs"
                    role="tablist"
                    aria-orientation="vertical"
                >
                    <a
                        class="nav-link active"
                        id="product-description-tab"
                        data-bs-toggle="pill"
                        type="button"
                        href="#product-description"
                        role="tab"
                        aria-controls="product-description"
                        aria-selected="true"
                    >
                        {{ __('Description') }}
                    </a>
                    @if (EcommerceHelper::isProductSpecificationEnabled() && $product->specificationAttributes->where('pivot.hidden', false)->isNotEmpty())
                        <a
                            class="nav-link"
                            id="tab-specification-tab"
                            data-bs-toggle="pill"
                            type="button"
                            href="#tab-specification"
                            role="tab"
                            aria-controls="tab-specification"
                            aria-selected="true"
                        >
                            {{ __('Specification') }}
                        </a>
                    @endif
                    @if (EcommerceHelper::isReviewEnabled())
                        <a
                            class="nav-link"
                            id="product-reviews-tab"
                            data-bs-toggle="pill"
                            type="button"
                            href="#product-reviews"
                            role="tab"
                            aria-controls="product-reviews"
                            aria-selected="false"
                        >
                            {{ __('Reviews') }} ({{ $product->reviews_count }})
                        </a>
                    @endif
                    @if (is_plugin_active('marketplace') && $product->store_id)
                        <a
                            class="nav-link"
                            id="product-vendor-info-tab"
                            data-bs-toggle="pill"
                            type="button"
                            href="#product-vendor-info"
                            role="tab"
                            aria-controls="product-vendor-info"
                            aria-selected="false"
                        >
                            {{ __('Vendor Info') }}
                        </a>
                    @endif
                    @if (is_plugin_active('faq') && count($product->faq_items) > 0)
                        <a
                            class="nav-link"
                            id="product-faqs-tab"
                            data-bs-toggle="pill"
                            type="button"
                            href="#product-faqs"
                            role="tab"
                            aria-controls="product-faqs"
                            aria-selected="false"
                        >
                            {{ __('Questions & Answers') }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="col-md-9">
                <div
                    class="tab-content"
                    id="product-detail-tabs-content"
                >
                    <div
                        class="tab-pane fade show active"
                        id="product-description"
                        role="tabpanel"
                        aria-labelledby="product-description-tab"
                    >
                        <div class="ck-content">
                            {!! BaseHelper::clean($product->content) !!}
                        </div>

                        {!! apply_filters(BASE_FILTER_PUBLIC_COMMENT_AREA, null, $product) !!}
                    </div>

                    @if (EcommerceHelper::isProductSpecificationEnabled() && $product->specificationAttributes->where('pivot.hidden', false)->isNotEmpty())
                        <div class="tab-pane fade" id="tab-specification" role="tabpanel"
                             aria-labelledby="tab-specification-tab">
                            <div class="tp-product-details-additional-info">
                                @include(EcommerceHelper::viewPath('includes.product-specification'))
                            </div>
                        </div>
                    @endif

                    @if (EcommerceHelper::isReviewEnabled())
                        <div
                            class="tab-pane fade"
                            id="product-reviews"
                            role="tabpanel"
                            aria-labelledby="product-reviews-tab"
                        >
                            @include('plugins/ecommerce::themes.includes.reviews')
                        </div>
                    @endif
                    @if (is_plugin_active('marketplace') && $product->store_id)
                        <div
                            class="tab-pane fade"
                            id="product-vendor-info"
                            role="tabpanel"
                            aria-labelledby="product-vendor-info-tab"
                        >
                            @include(Theme::getThemeNamespace() . '::views.marketplace.includes.info-box', [
                                'store' => $product->store,
                            ])
                        </div>
                    @endif
                    @if (is_plugin_active('faq') && count($product->faq_items) > 0)
                        <div
                            class="tab-pane fade"
                            id="product-faqs"
                            role="tabpanel"
                            aria-labelledby="product-faqs-tab"
                        >
                            @include('plugins/ecommerce::themes.includes.product-faqs', ['faqs' => $product->faq_items])
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if (($relatedProducts = get_related_products($product, 6)) && $relatedProducts->isNotEmpty())
    @php
        // Display controls for related products - show all elements by default
        $showTitle = true;
        $showRating = true;
        $showPrice = true;
        $showDescription = false; // Keep descriptions hidden to save space
        $showStoreInfo = false; // Keep store info hidden to save space
        $showLabels = true;
        $showAddToCart = true;
        $showWishlist = true;
    @endphp
    <div class="widget-products-with-category py-5 bg-light">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-0 py-2">{{ __('Related products') }}</h2>
                </div>
                <div class="product-deals-day__body arrows-top-right">
                    <div
                        class="product-deals-day-body slick-slides-carousel"
                        data-slick="{{ json_encode([
                            'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
                            'appendArrows' => '.arrows-wrapper',
                            'arrows' => true,
                            'dots' => false,
                            'autoplay' => false,
                            'infinite' => false,
                            'autoplaySpeed' => 3000,
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
                        @foreach ($relatedProducts as $relatedProduct)
                            <div class="product-inner">
                                {!! Theme::partial('ecommerce.product-item-featured', ['product' => $relatedProduct, 'wishlistIds' => $wishlistIds ?? [], 'showTitle' => $showTitle, 'showRating' => $showRating, 'showPrice' => $showPrice, 'showDescription' => $showDescription, 'showStoreInfo' => $showStoreInfo, 'showLabels' => $showLabels, 'showAddToCart' => $showAddToCart, 'showWishlist' => $showWishlist]) !!}
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
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
.product-deals-day-body .product-inner .quantity {
    display: flex !important;
    align-items: center !important;
    margin-bottom: 0 !important;
    margin-right: 8px !important;
}
.product-deals-day-body .product-inner .quantity .label-quantity {
    display: none !important; /* Hide quantity label to save space */
}
.product-deals-day-body .product-inner .quantity .qty-box {
    display: flex !important;
    align-items: center !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    width: 80px !important; /* Smaller width for related products */
    height: 32px !important;
}
.product-deals-day-body .product-inner .quantity .qty-box input {
    text-align: center !important;
    border: none !important;
    padding: 4px !important;
    font-size: 12px !important;
    width: 100% !important;
}
.product-deals-day-body .product-inner .quantity .qty-box .svg-icon {
    padding: 4px !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 20px !important;
    height: 20px !important;
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
</style>
@endif

<div id="sticky-add-to-cart">
    <header class="header--product js-product-content">
        <nav class="navigation">
            <div class="container">
                <article class="ps-product--header-sticky">
                    <div class="ps-product__thumbnail">
                        <img
                            src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}"
                            alt="{{ $product->name }}"
                        >
                    </div>
                    <div class="ps-product__wrapper">
                        <div class="ps-product__content">
                            <span class="ps-product__title">{!! BaseHelper::clean($product->name) !!}</span>
                            <ul>
                                <li class="active"><a href="#product-description-tab">{{ __('Description') }}</a>
                                </li>
                                @if (EcommerceHelper::isReviewEnabled())
                                    <li><a href="#product-reviews-tab">{{ __('Reviews') }}
                                            ({{ $product->reviews_count }})</a></li>
                                @endif
                            </ul>
                        </div>
                        <div class="ps-product__shopping">
                            @if ($isQuoteMode)
                                {{-- Quote Mode - Show "Contact for Price" instead of price --}}
                                <div class="quote-price-display">
                                    <span class="text-primary fw-bold">{{ __('Contact for Price') }}</span>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-primary ms-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#productQuoteModal"
                                    title="{{ __('Request Quote') }}"
                                >
                                    <span class="svg-icon">
                                        <svg width="16" height="16" fill="currentColor">
                                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                        </svg>
                                    </span>
                                    <span class="add-to-cart-text ms-1">{{ __('Request Quote') }}</span>
                                </button>
                            @else
                                {{-- Standard Cart Mode --}}
                                {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
                                @if (EcommerceHelper::isCartEnabled())
                                    <button
                                        class="btn btn-primary ms-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                                        name="add_to_cart"
                                        type="button"
                                        value="1"
                                        title="{{ __('Add to cart') }}"
                                        @if ($product->isOutOfStock()) disabled @endif
                                    >
                                        <span class="svg-icon">
                                            <svg>
                                                <use
                                                    href="#svg-icon-cart"
                                                    xlink:href="#svg-icon-cart"
                                                ></use>
                                            </svg>
                                        </span>
                                        <span class="add-to-cart-text ms-1">{{ __('Add to cart') }}</span>
                                    </button>
                                    @if (EcommerceHelper::isQuickBuyButtonEnabled())
                                        <button
                                            class="btn btn-primary btn-black ms-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                                            name="checkout"
                                            type="button"
                                            value="1"
                                            title="{{ __('Buy Now') }}"
                                            @if ($product->isOutOfStock()) disabled @endif
                                        >
                                            <span class="add-to-cart-text">{{ __('Buy Now') }}</span>
                                        </button>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        </nav>
    </header>

    <div class="sticky-atc-wrap sticky-atc-shown">
        <div class="container">
            <div class="row">
                <div class="sticky-atc-btn product-button">
                    @if ($isQuoteMode)
                        {{-- Quote Mode - Show quote button --}}
                        <button
                            type="button"
                            class="btn btn-primary mb-2"
                            data-bs-toggle="modal"
                            data-bs-target="#productQuoteModal"
                            title="{{ __('Request Quote') }}"
                        >
                            <span class="svg-icon">
                                <svg width="16" height="16" fill="currentColor">
                                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                </svg>
                            </span>
                            <span class="add-to-cart-text ms-1">{{ __('Request Quote') }}</span>
                        </button>
                    @else
                        {{-- Standard Cart Mode --}}
                        @if (EcommerceHelper::isCartEnabled())
                            <button
                                class="btn btn-primary mb-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                                name="add_to_cart"
                                type="button"
                                value="1"
                                title="{{ __('Add to cart') }}"
                                @if ($product->isOutOfStock()) disabled @endif
                            >
                                <span class="svg-icon">
                                    <svg>
                                        <use
                                            href="#svg-icon-cart"
                                            xlink:href="#svg-icon-cart"
                                        ></use>
                                    </svg>
                                </span>
                                <span class="add-to-cart-text ms-1">{{ __('Add to cart') }}</span>
                            </button>

                            @if (EcommerceHelper::isQuickBuyButtonEnabled())
                                <button
                                    class="btn btn-primary btn-black mb-2 ms-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                                    name="checkout"
                                    type="button"
                                    value="1"
                                    title="{{ __('Buy Now') }}"
                                    @if ($product->isOutOfStock()) disabled @endif
                                >
                                    <span class="add-to-cart-text ms-2">{{ __('Buy Now') }}</span>
                                </button>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Mobile & Tablet Product Details Page Styles - Desktop Excluded */

/* Tablet Responsive Adjustments (768px - 1199px) */
@media (max-width: 1199px) and (min-width: 768px) {
    .product-details .product-button {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
        width: 100% !important;
    }
    
    .product-details .quantity {
        display: flex !important;
        align-items: center !important;
        flex-shrink: 0 !important;
        margin-bottom: 10px !important;
        width: 100% !important;
        justify-content: flex-start !important;
    }
    
    .product-details .quantity .label-quantity {
        margin-bottom: 0 !important;
        margin-right: 8px !important;
        white-space: nowrap !important;
        font-size: 14px !important;
    }
    
    .product-details .quantity .qty-box {
        display: flex !important;
        align-items: center !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
        width: 120px !important;
        flex-shrink: 0 !important;
    }
    
    .product-details .quantity .qty-box input {
        text-align: center !important;
        border: none !important;
        padding: 8px 4px !important;
        font-size: 14px !important;
    }
    
    .product-details .quantity .qty-box .svg-icon {
        padding: 8px !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .product-details .add-to-cart-button,
    .product-details .buy-now-button {
        flex: 1 1 calc(50% - 4px) !important;
        min-width: auto !important;
        padding: 10px 12px !important;
        font-size: 14px !important;
        white-space: nowrap !important;
        margin-bottom: 0 !important;
        margin-left: 0 !important;
    }
    
    .product-details .add-to-cart-text {
        display: inline !important;
        margin-left: 4px !important;
    }
    
    .product-details .btn-black {
        background-color: #333 !important;
        border-color: #333 !important;
    }
    
    .product-details .product-loop-buttons {
        display: flex !important;
        gap: 8px !important;
        flex-shrink: 0 !important;
        margin-top: 10px !important;
    }
    
    .product-details .product-loop-buttons .btn {
        padding: 10px !important;
        min-width: auto !important;
    }
    
    .product-details-content {
        padding-left: 15px !important;
    }
}

/* Mobile Responsive Adjustments (max-width: 767px) */
@media (max-width: 767px) {
    .product-details .product-button {
        display: flex !important;
        flex-direction: column !important;
        gap: 12px !important;
        align-items: stretch !important;
        width: 100% !important;
    }
    
    .product-details .quantity {
        display: flex !important;
        align-items: center !important;
        flex-shrink: 0 !important;
        margin-bottom: 12px !important;
        justify-content: center !important;
    }
    
    .product-details .quantity .label-quantity {
        margin-bottom: 0 !important;
        margin-right: 8px !important;
        white-space: nowrap !important;
        font-size: 14px !important;
    }
    
    .product-details .quantity .qty-box {
        display: flex !important;
        align-items: center !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
        width: 120px !important;
        flex-shrink: 0 !important;
    }
    
    .product-details .quantity .qty-box input {
        text-align: center !important;
        border: none !important;
        padding: 8px 4px !important;
        font-size: 14px !important;
    }
    
    .product-details .quantity .qty-box .svg-icon {
        padding: 8px !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .product-details .add-to-cart-button,
    .product-details .buy-now-button {
        width: 100% !important;
        flex: none !important;
        margin-left: 0 !important;
        margin-bottom: 8px !important;
        padding: 10px 12px !important;
        font-size: 14px !important;
        white-space: nowrap !important;
    }
    
    .product-details .add-to-cart-text {
        display: inline !important;
        margin-left: 4px !important;
    }
    
    .product-details .btn-black {
        background-color: #333 !important;
        border-color: #333 !important;
    }
    
    .product-details .product-loop-buttons {
        display: flex !important;
        gap: 8px !important;
        flex-shrink: 0 !important;
        justify-content: center !important;
        margin-top: 12px !important;
    }
    
    .product-details .product-loop-buttons .btn {
        padding: 10px !important;
        min-width: auto !important;
    }
    
    .product-details-content {
        padding-left: 0 !important;
    }
    
    .product-detail-container .row {
        margin: 0 !important;
    }
    
    .product-detail-container .col-lg-5,
    .product-detail-container .col-lg-4 {
        padding: 0 15px !important;
    }
}

/* Small Mobile Devices (max-width: 576px) */
@media (max-width: 576px) {
    .product-details .quantity .qty-box {
        width: 100px !important;
    }
    
    .product-details .quantity .label-quantity {
        font-size: 13px !important;
        margin-right: 6px !important;
    }
    
    .product-details .add-to-cart-button,
    .product-details .buy-now-button {
        padding: 12px 16px !important;
        font-size: 15px !important;
    }
    
    .product-details .product-button {
        gap: 15px !important;
    }
    
    .product-detail-container .container-xxxl {
        padding: 15px !important;
        margin: 10px !important;
    }
}

/* Sticky Add to Cart Mobile Adjustments */
@media (max-width: 767px) {
    .sticky-atc-btn.product-button {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        align-items: stretch !important;
    }
    
    .sticky-atc-btn .add-to-cart-button {
        width: 100% !important;
        margin: 0 !important;
        margin-bottom: 8px !important;
    }
}

.variation-btn.active {
    border: 2px solid #000;
    outline: none;
}
.color-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    border: 1px solid #ccc;
}
.size-pills .variation-btn {
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid #ccc;
}

</style>