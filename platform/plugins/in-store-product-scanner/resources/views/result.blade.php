<div class="scan-result">
  <h2>{{ $item['name'] ?? 'Unknown product' }}</h2>
  <div class="meta">
    <strong>SKU:</strong> {{ $item['sku'] ?? '-' }}
    <strong>Barcode:</strong> {{ $item['barcode'] ?? '-' }}
  </div>
  <div class="prices">
    <span class="price">Price: {{ $item['price'] ?? '-' }}</span>
    @if(! empty($item['sale_price']))
      <span class="sale">Sale: {{ $item['sale_price'] }}</span>
      <span class="discount">({{ $item['discount_percentage'] }}% off)</span>
    @endif
  </div>
  <div class="stock">Stock: {{ $item['in_stock'] ?? 'N/A' }}</div>
  @if(! empty($item['image']))
    <div class="image"><img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"/></div>
  @endif
  @if(! empty($item['variants']))
    <div class="variants">
      <label>Variants:</label>
      <select id="variantSelect">
        @foreach($item['variants'] as $v)
          <option value="{{ $v['id'] }}" data-price="{{ $v['price'] }}" data-stock="{{ $v['in_stock'] }}">{{ $v['sku'] ?? $v['id'] }} - {{ $v['price'] }}</option>
        @endforeach
      </select>
    </div>
  @endif
</div>
