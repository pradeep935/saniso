@if ($product->variations_count > 0 && !$product->is_variation)
    <span class="text-muted">&mdash;</span>
@else
    @if($branchInventory)
        <!-- Existing Branch Inventory - Editable Quantity -->
        <input type="number" 
               value="{{ $branchInventory->quantity_on_hand }}" 
               class="form-control branch-inventory-editable" 
               data-inventory-id="{{ $branchInventory->id }}"
               data-field="quantity_on_hand"
               data-product-id="{{ $product->id }}"
               data-branch-id="{{ $branchId }}"
               min="0" 
               step="1"
               class="input-min-width">
        <small class="text-muted">Available: {{ $branchInventory->quantity_available }}</small>
    @else
        <!-- No Branch Inventory - Show Add Button -->
        <div class="d-flex align-items-center">
            <span class="text-warning me-2">Not in inventory</span>
            <button class="btn btn-sm btn-outline-primary add-to-inventory-btn" 
                    data-product-id="{{ $product->id }}"
                    data-branch-id="{{ $branchId }}"
                    title="Add to branch inventory">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    @endif
@endif