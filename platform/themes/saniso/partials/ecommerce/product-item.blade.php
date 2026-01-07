@php use Illuminate\Support\Str; @endphp
@php
    use Botble\Ecommerce\Models\QuoteSettings;
    
    // Check if this product is quote-only (price is 0)
    $isQuoteOnly = $product->price <= 0;
    
    // Check if this product is in quote mode (from existing quote settings)
    $isQuoteMode = QuoteSettings::shouldShowQuoteForm($product);
    
    // Combine both conditions - show quote mode if either condition is true
    $showQuoteMode = $isQuoteOnly || $isQuoteMode;
    
    // Optional display flags (can be passed from shortcodes/partials)
    $showTitle = $showTitle ?? true;
    $showRating = $showRating ?? EcommerceHelper::isReviewEnabled();
    $showPrice = $showQuoteMode ? false : ($showPrice ?? true); // Hide price in quote mode
    $showDescription = $showDescription ?? true;
    $showStoreInfo = $showStoreInfo ?? false;
    $showLabels = $showLabels ?? true;
    $showAddToCart = $showQuoteMode ? false : ($showAddToCart ?? true); // Hide add to cart in quote mode
    $showWishlist = $showWishlist ?? true;
@endphp
<div class="product-thumbnail">
    <a
        class="product-loop__link img-fluid-eq"
        href="{{ $product->url }}"
        tabindex="0"
    >
        <div class="img-fluid-eq__dummy"></div>
        <div class="img-fluid-eq__wrap">
            <img
                class="lazyload product-thumbnail__img"
                data-src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}"
                src="{{ image_placeholder($product->image, 'small') }}"
                alt="{{ $product->name }}"
            >
        </div>
        @if ($showLabels)
            <span class="ribbons">
            @if ($product->isOutOfStock())
                <span class="ribbon out-stock">{{ __('Out Of Stock') }}</span>
            @else
                @if ($product->productLabels->isNotEmpty())
                    @foreach ($product->productLabels as $label)
                        <span
                            class="ribbon"
                            {!! $label->css_styles !!}
                        >{{ $label->name }}</span>
                    @endforeach
                @else
                    @if ($product->front_sale_price !== $product->price && !$showQuoteMode)
                        <div
                            class="featured ribbon"
                            dir="ltr"
                        >{{ get_sale_percentage($product->price, $product->front_sale_price) }}</div>
                    @endif
                @endif
            @endif
        </span>
        @endif
    </a>
    @if ($showWishlist || $showAddToCart)
        {!! Theme::partial(
            'ecommerce.product-loop-buttons',
            compact('product', 'showWishlist', 'showAddToCart') + (!empty($wishlistIds) ? compact('wishlistIds') : []),
        ) !!}
    @endif
</div>
<div class="product-details position-relative">
    <div class="product-content-box">
        @if ($showStoreInfo && is_plugin_active('marketplace') && $product->store->id)
            <div class="sold-by-meta">
                <a
                    href="{{ $product->store->url }}"
                    tabindex="0"
                >{{ $product->store->name }}</a>
            </div>
        @endif
        @if ($showTitle)
            <h3 class="product__title">
                <a
                    href="{{ $product->url }}"
                    tabindex="0"
                >{{ $product->name }}</a>
            </h3>
        @endif
        @if ($showRating && EcommerceHelper::isReviewEnabled())
            {!! Theme::partial('star-rating', ['avg' => $product->reviews_avg, 'count' => $product->reviews_count]) !!}
        @endif
        @if ($showPrice)
            {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
        @endif
        
        @if ($showQuoteMode)
            <div class="product-quote-section mt-2">
                <div class="quote-price-display">
                    <span class="text-primary fw-bold">
                        @if ($isQuoteOnly)
                            {{ __('Contact for Price') }}
                        @else
                            {{ __('Request Quote Available') }}
                        @endif
                    </span>
                </div>
                <a 
                    href="{{ $product->url }}" 
                    class="btn btn-primary btn-sm mt-2 w-100"
                    title="{{ __('View Product & Request Quote') }}"
                >
                    <i class="fas fa-quote-left me-1"></i>
                    {{ __('Request Quote') }}
                </a>
            </div>
        @endif
        {{-- Hide product description --}}
        @if (false && $showDescription && !empty($product->description))
            <div class="product-desc mt-2 small text-muted">
                {!! Str::words(strip_tags($product->description), 12) !!}
            </div>
        @endif
        @if (!empty($isFlashSale))
            <div class="deal-sold row mt-2">
                @if (Botble\Ecommerce\Facades\FlashSale::isShowSaleCountLeft())
                    <div class="deal-text col-auto">
                        <span class="sold fw-bold">
                            @if ($product->pivot->quantity > $product->pivot->sold)
                                <span class="text">{{ __('Sold') }}: </span>
                                <span class="value">{{ (int) $product->pivot->sold }} /
                                    {{ (int) $product->pivot->quantity }}</span>
                            @else
                                <span class="text text-danger">{{ __('Sold out') }}</span>
                            @endif
                        </span>
                    </div>
                @endif
                <div class="deal-progress col">
                    <div class="progress">
                        <div
                            class="progress-bar"
                            role="progressbar"
                            aria-label="{{ __('Sold out') }}"
                            aria-valuenow="{{ $product->pivot->quantity > 0 ? ($product->pivot->sold / $product->pivot->quantity) * 100 : 0 }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            style="width: {{ $product->pivot->quantity > 0 ? ($product->pivot->sold / $product->pivot->quantity) * 100 : 0 }}%"
                        >
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @if ($showAddToCart)
        <div class="product-bottom-box">
            {!! Theme::partial('ecommerce.product-cart-form', compact('product')) !!}
        </div>
    @endif
</div>
