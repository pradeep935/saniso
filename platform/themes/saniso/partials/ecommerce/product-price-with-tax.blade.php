@if (true)
    @php
        // Check if this is a quote-only product (price is 0)
        $isQuoteOnly = $product->price <= 0;
    @endphp
    
    @if ($isQuoteOnly)
        {{-- Quote-only product - show contact for price --}}
        <div class="product-quote-price">
            <span class="text-primary fw-bold">{{ __('Contact for Price') }}</span>
        </div>
    @else
        {{-- Regular product with price - show normal price display --}}
        @php
            if (!function_exists('calculatePriceWithTax')) {
                function calculatePriceWithTax($product) {
                    $basePrice = $product->front_sale_price; // sale or normal
                    $originalPrice = $product->price;       // original price

                    $taxPercentage = 0;

                    if (\Botble\Ecommerce\Facades\EcommerceHelper::isTaxEnabled()) {
                        $taxModel = null;

                        // ✅ 1. Priority: product-specific tax
                        if ($product->tax_id) {
                            $taxModel = \Botble\Ecommerce\Models\Tax::query()
                                ->where('id', $product->tax_id)
                                ->where('status', \Botble\Base\Enums\BaseStatusEnum::PUBLISHED)
                                ->first();
                        }

                        // ✅ 2. Fallback: default tax if no product tax
                        if (!$taxModel) {
                            if ($defaultTaxRate = get_ecommerce_setting('default_tax_rate')) {
                                $taxModel = \Botble\Ecommerce\Models\Tax::query()
                                    ->where('id', $defaultTaxRate)
                                    ->where('status', \Botble\Base\Enums\BaseStatusEnum::PUBLISHED)
                                    ->first();
                            }
                        }

                        // ✅ 3. Apply tax if found
                        if ($taxModel) {
                            $taxPercentage = $taxModel->percentage;
                        }
                    }

                    // Calculate
                    $priceExclTax = $basePrice;
                    $priceInclTax = $taxPercentage > 0
                        ? $basePrice + ($basePrice * ($taxPercentage / 100))
                        : $basePrice;

                    $currency = get_application_currency();
                    $currencySymbol = $currency ? $currency->symbol : '€';

                    return [
                        'price_incl_tax' => $currencySymbol . ' ' . number_format($priceInclTax, 2, ',', '.'),
                        'price_excl_tax' => $currencySymbol . ' ' . number_format($priceExclTax, 2, ',', '.'),
                        'tax_percentage' => $taxPercentage,
                        'original_price' => $originalPrice,
                    ];
                }
            }

            $priceInfo = calculatePriceWithTax($product);
        @endphp

        <div class="product-price-with-tax">
            {{-- If sale price is active, show strike-through original --}}
            @if ($product->front_sale_price < $product->price)
                <div class="old-price">
                    <s>{{ format_price($priceInfo['original_price']) }}</s>
                </div>
            @endif

            {{-- Incl. tax --}}
            <div>
                {{ $priceInfo['price_incl_tax'] }}
                @if($priceInfo['tax_percentage'] > 0)
                    <span>(Incl. {{ $priceInfo['tax_percentage'] }}%)</span>
                @endif
            </div>

            {{-- Excl. tax --}}
            @if($priceInfo['tax_percentage'] > 0)
                <div>
                    {{ $priceInfo['price_excl_tax'] }}
                    <span>(Excl. {{ $priceInfo['tax_percentage'] }}%)</span>
                </div>
            @endif
        </div>
    @endif
@endif