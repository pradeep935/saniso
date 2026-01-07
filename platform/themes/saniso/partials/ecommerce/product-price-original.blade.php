@if (! EcommerceHelper::hideProductPrice() || EcommerceHelper::isCartEnabled())
    @php
        $currency = get_application_currency();
        $currencySymbol = $currency ? $currency->symbol : '€';
    @endphp
    <style>
    .product-price .amount {
        color: #123779 !important;
        font-weight: bold;
    }
    </style>
    <span class="product-price">
        <span class="product-price-sale bb-product-price @if (!$product->isOnSale()) d-none @endif">
            <ins>
                <span class="price-amount">
                    <bdi>
                        <span class="amount bb-product-price-text" data-bb-value="product-price">{{ $currencySymbol }} {{ number_format($product->front_sale_price_with_taxes, 2, ',', '.') }}</span>
                    </bdi>
                </span>
            </ins>
            &nbsp;
            <del aria-hidden="true">
                <span class="price-amount">
                    <bdi>
                        <span class="amount bb-product-price-text-old" data-bb-value="product-original-price">{{ $currencySymbol }} {{ number_format($product->price_with_taxes, 2, ',', '.') }}</span>
                    </bdi>
                </span>
            </del>
        </span>
        <span class="product-price-original bb-product-price @if ($product->isOnSale()) d-none @endif">
            <span class="price-amount">
                <bdi>
                    <span class="amount bb-product-price-text" data-bb-value="product-price">{{ $currencySymbol }} {{ number_format($product->front_sale_price_with_taxes, 2, ',', '.') }}</span>
                </bdi>
            </span>
        </span>
    </span>
@endif
