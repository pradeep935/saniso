<div class="recently-product-wrapper">
    @if ($products->isNotEmpty())
        <ul
            class="product-list"
            data-slick="{{ json_encode(['arrows' => true, 'dots' => false, 'autoplay' => false, 'infinite' => true, 'slidesToShow' => 10]) }}"
        >
            @foreach ($products as $product)
                <li class="product">
                    <a href="{{ $product->url }}" class="recent-product-link">
                        <div class="recent-product-image-wrapper">
                            <img
                                src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                alt="{{ $product->name }}"
                            />
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <div class="recently-empty-products text-center">
            <div class="empty-desc">
                <span>{{ __('Recently Viewed Products is a function which helps you keep track of your recent viewing history.') }}</span>
                <a
                    class="text-primary"
                    href="{{ route('public.products') }}"
                >{{ __('Shop Now') }}</a>
            </div>
        </div>
    @endif
</div>

<style>
/* Recently viewed products - Equal size images */
.recently-product-wrapper .product-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 10px;
}

.recently-product-wrapper .product {
    flex: 0 0 auto;
    width: 80px; /* Fixed width for consistency */
}

.recently-product-wrapper .recent-product-image-wrapper {
    width: 80px;
    height: 80px; /* Square containers for consistency */
    overflow: hidden;
    border-radius: 8px;
    border: none; /* Remove border to prevent double border */
    background-color: #f8f9fa; /* Light gray background for better contrast */
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px; /* Small padding for better image display */
}


.recently-product-wrapper .recent-product-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: contain; /* Show full image without cropping */
}

/* Override any external hover styles */
.recently-product-wrapper .recent-product-link:hover,
.recently-product-wrapper .recent-product-link:focus,
.recently-product-wrapper .recent-product-image-wrapper:hover,
.recently-product-wrapper .recent-product-image-wrapper:focus,
.recently-product-wrapper .product:hover,
.recently-product-wrapper .product:focus {
    border: none !important;
    box-shadow: none !important;
    transform: none !important;
    background: none !important;
    outline: none !important;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .recently-product-wrapper .product {
        width: 60px;
    }
    
    .recently-product-wrapper .recent-product-image-wrapper {
        width: 60px;
        height: 60px;
    }
    
    .recently-product-wrapper .product-list {
        gap: 8px;
    }
}

/* Slick carousel adjustments for equal sizing */
.recently-product-wrapper .slick-slide {
    width: auto !important;
}

.recently-product-wrapper .slick-track {
    display: flex !important;
    align-items: center;
}
</style>
