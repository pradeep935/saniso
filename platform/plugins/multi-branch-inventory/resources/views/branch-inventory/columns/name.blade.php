@php
    $isVariation = $product->is_variation;
    $hasVariations = $product->variations_count > 0 && !$isVariation;
    $variationAttributes = '';
    
    // Get variation attributes if this is a variation
    if ($isVariation && $product->variationInfo) {
        $attributes = [];
        foreach ($product->variationInfo->variationItems as $item) {
            $attributes[] = $item->attribute->title . ': ' . $item->title;
        }
        $variationAttributes = implode(', ', $attributes);
    }
@endphp

<div class="product-name-display">
    @if($isVariation)
        <!-- Variation Product -->
        <div class="d-flex align-items-center">
            <i class="fas fa-level-up-alt me-2 text-muted icon-rotate-90"></i>
            <div>
                <div class="fw-bold">{{ $product->name }}</div>
                @if($variationAttributes)
                    <small class="text-primary">{{ $variationAttributes }}</small><br>
                @endif
                <small class="text-muted">
                    <i class="fas fa-sitemap me-1"></i>
                    Parent: {{ $product->parent->name ?? 'N/A' }}
                </small>
            </div>
        </div>
    @elseif($hasVariations)
        <!-- Parent Product with Variations -->
        <div>
            <div class="fw-bold">{{ $product->name }}</div>
            <small class="text-info">
                <i class="fas fa-layer-group me-1"></i>
                {{ $product->variations_count }} variation{{ $product->variations_count > 1 ? 's' : '' }}
            </small>
        </div>
    @else
        <!-- Simple Product -->
        <div class="fw-bold">{{ $product->name }}</div>
    @endif
</div>