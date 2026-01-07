@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<style>
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    #loadingOverlay.loading-overlay-hidden {
        display: none !important;
    }
    
    .loading-content {
        background: white;
        padding: 20px 40px;
        border-radius: 8px;
        text-align: center;
        font-size: 16px;
    }
    
    .loading-content i {
        margin-right: 10px;
        color: #007bff;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">Inventory Management</h3>
                            <p class="text-muted mb-0">Manage product quantities across all branches</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branch Filter -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Select Branch</label>
                            <select name="branch_id" class="form-select" onchange="this.form.submit()">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $selectedBranch && $selectedBranch->id == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Search Products</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="form-control" placeholder="Search by name or EAN...">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Search Mode</label>
                            <select name="search_mode" class="form-select">
                                <option value="name" {{ request('search_mode', 'name') === 'name' ? 'selected' : '' }}>By Name</option>
                                <option value="ean" {{ request('search_mode', 'name') === 'ean' ? 'selected' : '' }}>By EAN</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Sort By</label>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="name_asc" {{ request('sort', 'name_asc') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                                <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                                <option value="qty_low" {{ request('sort') === 'qty_low' ? 'selected' : '' }}>Qty (Low-High)</option>
                                <option value="qty_high" {{ request('sort') === 'qty_high' ? 'selected' : '' }}>Qty (High-Low)</option>
                                <option value="sku_asc" {{ request('sort') === 'sku_asc' ? 'selected' : '' }}>SKU (A-Z)</option>
                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price (Low-High)</option>
                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price (High-Low)</option>
                            </select>
                        </div>
                    </form>
                    <div class="row g-2 mt-2 align-items-center">
                        <div class="col-md-6"></div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" onclick="document.querySelector('form').submit()">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                        </div>
                        <!-- Add All button removed as per configuration (no mass-add) -->
                        @if($selectedBranch && $selectedBranch->is_main_branch)
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger w-100" onclick="restockMainBranchZero()">
                                <i class="fas fa-warehouse me-1"></i> Main: 0→1000
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($selectedBranch)
                @php
                    $invCollection = method_exists($inventory, 'getCollection') ? $inventory->getCollection() : ($inventory ?? collect());
                    $replenishItems = $invCollection->filter(function ($i) {
                        return isset($i->needs_replenishment) && $i->needs_replenishment;
                    })->take(5);
                @endphp

                @if($replenishItems->count() > 0)
                    <div class="alert alert-warning d-flex justify-content-between align-items-start">
                        <div>
                            <strong><i class="fas fa-exclamation-triangle me-2"></i>Low stock alert</strong>
                            <div class="small text-muted">{{ $replenishItems->count() }} items need replenishment in {{ $selectedBranch->name }}. Top items:</div>
                            <ul class="mb-0 mt-2">
                                @foreach($replenishItems as $ri)
                                    <li>
                                        @if(isset($ri->id) && $ri->id)
                                            <a href="{{ route('branch-inventory.edit', $ri->id) }}">{{ $ri->product->name ?? 'Product #' . ($ri->product_id ?? 'N/A') }}</a>
                                        @else
                                            {{ $ri->product->name ?? 'Product #' . ($ri->product_id ?? 'N/A') }}
                                        @endif
                                        — Replenish {{ number_format($ri->replenishment_quantity ?? 0) }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('branch-inventory.index', ['branch_id' => $selectedBranch->id]) }}" class="btn btn-sm btn-outline-danger">View all</a>
                        </div>
                    </div>
                @endif
            <!-- Products Table -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $selectedBranch->name }} - Products</h5>
                        <div>
                            <small class="text-muted me-3">{{ ($inventory ?? collect())->total() }} products</small>
                            @if(isset($stats['replenishment_requests']) && $stats['replenishment_requests'] > 0)
                                <span class="badge bg-danger">Replenishment: {{ $stats['replenishment_requests'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if(($inventory ?? collect())->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th width="8%">ID</th>
                                        <th width="50%">Product Name</th>
                                        <th width="15%" class="text-center">Branch Quantity</th>
                                        <th width="15%" class="text-center">Global Quantity</th>
                                        <th width="12%" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inventory ?? [] as $item)
                                        @php
                                            // The controller has already transformed the data
                                            // $item contains the branch inventory data for the selected branch
                                            $branchInventory = $item->id ? $item : null;
                                            $product = $item->product; // Get the original product from transformed data
                                            
                                            // Use the product's main quantity field as global quantity
                                            $globalQuantity = $product->quantity ?: 0;
                                        @endphp
                                        <tr @if(isset($item->needs_replenishment) && $item->needs_replenishment) class="table-warning" @endif>
                                            <td>{{ $product->id }}</td>
                                            
                                            <td>
                                                @if (! $product->is_variation)
                                                    <div class="d-block">
                                                        <div class="mb-1">
                                                            <strong>{{ $product->name }}</strong>
                                                            @if(isset($item->needs_replenishment) && $item->needs_replenishment)
                                                                <span class="badge bg-danger ms-2">Replenish: {{ number_format($item->replenishment_quantity ?? 0) }}</span>
                                                            @endif
                                                            @if ($product->variations_count > 0)
                                                                <span class="badge bg-label ms-2">{{ $product->variations_count }} variations</span>
                                                            @endif
                                                        </div>
                                                        @if ($product->sku)
                                                            <div class="d-block text-muted small">SKU: {{ $product->sku }}</div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-start justify-content-start">
                                                        <div class="me-1 text-muted">↳</div>
                                                        <div>
                                                            @if($product->variation_attributes)
                                                                <div class="d-block text-success mb-1">{{ $product->variation_attributes }}</div>
                                                            @endif
                                                            @if ($product->sku)
                                                                <div class="d-block text-muted small">SKU: {{ $product->sku }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                @if($product->is_variation || !isset($product->variations_count) || $product->variations_count == 0)
                                                    {{-- Show quantity input only for variations or simple products (no variations) --}}
                                                    <input type="number" 
                                                           value="{{ $branchInventory ? ($branchInventory->quantity_available ?: 0) : 0 }}" 
                                                           class="form-control text-center quantity-input" 
                                                           data-inventory-id="{{ $branchInventory ? $branchInventory->id : '' }}"
                                                           data-product-id="{{ $product->id }}"
                                                           data-branch-id="{{ $selectedBranch->id }}"
                                                           class="w-100-inline"
                                                           min="0"
                                                           title="Inventory ID: {{ $branchInventory ? $branchInventory->id : 'none' }} | Product: {{ $product->id }} | Branch: {{ $selectedBranch->id }}">
                                                @else
                                                    {{-- Parent product with variations - no quantity input needed --}}
                                                    <span class="text-muted small">Manage via variations</span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                {{-- Always show product's database quantity field --}}
                                                <span class="badge bg-label" title="Product database quantity">
                                                    {{ number_format($product->quantity ?: 0) }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                @if($product->is_variation || !isset($product->variations_count) || $product->variations_count == 0)
                                                    {{-- Show actions only for variations or simple products --}}
                                                    @if($branchInventory)
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-outline-primary" 
                                                                    onclick="editInventory({{ $branchInventory->id }})" 
                                                                    title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-outline-danger" 
                                                                    onclick="removeFromBranch({{ $branchInventory->id }})" 
                                                                    title="Remove">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    @else
                                                        @if($selectedBranch->is_main_branch)
                                                            {{-- Main branch should always have products - auto-created above --}}
                                                            <span class="text-success small"><i class="fas fa-check"></i> Auto-added</span>
                                                        @else
                                                            {{-- Other branches can have "Add" button --}}
                                                            <button class="btn btn-outline-success btn-sm" 
                                                                    onclick="addToBranch({{ $product->id }}, {{ $selectedBranch->id }})" 
                                                                    title="Add to Branch">
                                                                <i class="fas fa-plus"></i> Add
                                                            </button>
                                                        @endif
                                                    @endif
                                                @else
                                                    {{-- Parent product with variations --}}
                                                    <span class="badge bg-light text-dark">{{ $product->variations_count }} items</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $inventory->links() ?? '' }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-box-open text-muted icon-large"></i>
                            <h5 class="mt-3 text-muted">No products found</h5>
                            <p class="text-muted">Try adjusting your search filters.</p>
                        </div>
                    @endif
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-building text-muted icon-large"></i>
                    <h5 class="mt-3 text-muted">Select a Branch</h5>
                    <p class="text-muted">Choose a branch from the filter above to view and manage its inventory.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay-hidden">
    <div class="loading-content">
        <i class="fas fa-spinner fa-spin"></i> Updating...
    </div>
</div>
@endsection

@push('footer')
<script>
$(document).ready(function() {
    // Auto-hide loading overlay after page load
    setTimeout(() => {
        $('#loadingOverlay').addClass('loading-overlay-hidden');
    }, 500);
    
    // Initialize quantity inputs - store original values
    $('.quantity-input').each(function() {
        $(this).data('original-value', $(this).val());
    });
    
    // Auto-save quantity changes
    $('.quantity-input').on('change', function() {
        const input = $(this);
        const inventoryId = input.data('inventory-id');
        const productId = input.data('product-id');
        const branchId = input.data('branch-id');
        const newQuantity = input.val();
        
        // Only update if value actually changed
        if (newQuantity != input.data('original-value')) {
            updateQuantity(input, inventoryId, newQuantity);
        }
    });
    $('.quantity-input').each(function() {
        $(this).data('original-value', $(this).val());
    });

    // Add product to branch with 100 quantity
    $('.add-product-btn').on('click', function() {
        const button = $(this);
        const productId = button.data('product-id');
        const branchId = button.data('branch-id');
        
        addProductToBranch(productId, branchId, 100);
    });
});

function updateQuantity(input, inventoryId, quantity) {
    if (input.data('processing')) {
        return;
    }
    
    input.data('processing', true);
    input.prop('disabled', true);
    $('#loadingOverlay').show();
    
    const numericQuantity = parseInt(quantity);
    
    if (isNaN(numericQuantity)) {
        showNotification('Please enter a valid number', 'error');
        return;
    }
    
    let updateData;
    
    if (inventoryId && inventoryId !== '') {
        updateData = {
            inventory_id: inventoryId,
            field: 'quantity_available',
            value: numericQuantity,
            _token: '{{ csrf_token() }}'
        };
    } else {
        const productId = input.data('product-id');
        const branchId = input.data('branch-id') || {{ $selectedBranch ? $selectedBranch->id : 'null' }};
        
        updateData = {
            product_id: productId,
            branch_id: branchId,
            quantity: numericQuantity,
            _token: '{{ csrf_token() }}'
        };
    }
    
    $.ajax({
        url: '{{ route("branch-inventory.update-quantity") }}',
        method: 'POST',
        data: updateData,
        success: function(response) {
            if (response.success) {
                const savedQty = response.saved_quantity;
                const globalQty = response.new_global_quantity;
                
                if (savedQty === undefined || savedQty === null) {
                    showNotification('Server error: No saved quantity returned', 'error');
                    return;
                }
                
                if (inventoryId && response.inventory_id && inventoryId != response.inventory_id) {
                    showNotification('Error: Response mismatch. Please refresh and try again.', 'error');
                    return;
                }
                
                input.val(savedQty);
                input.data('original-value', savedQty);
                
                if (globalQty !== undefined) {
                    const row = input.closest('tr');
                    const globalQtyElement = row.find('.badge.bg-label');
                    if (globalQtyElement.length) {
                        globalQtyElement.text(globalQty.toLocaleString());
                    }
                }
                
                input.addClass('border-success');
                setTimeout(() => input.removeClass('border-success'), 2000);
                
                showNotification('Saved: ' + savedQty + ' | Global: ' + (globalQty || 'N/A'), 'success');
            } else {
                showNotification('Failed to update quantity: ' + (response.message || 'Unknown error'), 'error');
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Unknown error';
            
            if (xhr.status === 422) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = 'Validation failed: ' + errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else {
                    errorMessage = 'Validation failed';
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                errorMessage = xhr.responseText.substring(0, 100) + '...';
            }
            
            showNotification('Error updating quantity: ' + errorMessage, 'error');
        },
        complete: function() {
            input.data('processing', false);
            input.prop('disabled', false);
            $('#loadingOverlay').hide();
        },
    });
}

function addProductToBranch(productId, branchId, quantity) {
    $('#loadingOverlay').show();
    
    $.ajax({
        url: '{{ route("branch-inventory.add-product-to-branch") }}',
        method: 'POST',
        data: {
            product_id: productId,
            branch_id: branchId,
            quantity_on_hand: quantity,
            quantity_available: quantity,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showNotification('Product added to branch successfully!', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showNotification('Failed to add product: ' + (response.message || 'Unknown error'), 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Unknown error';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showNotification('Error: ' + errorMessage, 'error');
        },
        complete: function() {
            $('#loadingOverlay').hide();
        }
    });
}

// addAllProductsToBranch removed — mass add disabled per requirements

function restockZeroQuantity() {
    const branchId = {{ $selectedBranch ? $selectedBranch->id : 'null' }};
    
    if (!branchId) {
        alert('Please select a branch first');
        return;
    }
    
    if (confirm('Set all products with 0 quantity to 1000?')) {
        $('#loadingOverlay').show();
        
        $.ajax({
            url: '{{ url("admin/branch-inventory/restock-zero") }}/' + branchId,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showNotification('Failed to restock products', 'error');
                }
            },
            error: function(xhr) {
                showNotification('Error: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
            },
            complete: function() {
                $('#loadingOverlay').hide();
            }
        });
    }
}

function restockMainBranchZero() {
    if (confirm('Set all 0-qty products in Main Branch to 1000?')) {
        $('#loadingOverlay').show();
        
        $.ajax({
            url: '{{ url("admin/branch-inventory/restock-main-zero") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showNotification('Failed to restock main branch', 'error');
                }
            },
            error: function(xhr) {
                showNotification('Error: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
            },
            complete: function() {
                $('#loadingOverlay').hide();
            }
        });
    }
}

function editInventory(inventoryId) {
    window.location.href = '/admin/branch-inventory/' + inventoryId + '/edit';
}

function removeFromBranch(inventoryId) {
    if (confirm('Remove this product from the branch?')) {
        $('#loadingOverlay').show();
        
        $.ajax({
            url: '/admin/branch-inventory/' + inventoryId,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                window.location.reload();
            },
            error: function(xhr) {
                showNotification('Error removing product', 'error');
            },
            complete: function() {
                $('#loadingOverlay').hide();
            }
        });
    }
}

function updateGlobalQuantity(row) {
    // This would make an AJAX call to get updated global quantity
    // For now, just reload the page to get fresh data
    // You could implement a more sophisticated update here
}

function addToBranch(productId, branchId) {
    if (confirm('Add this product to the selected branch with quantity 100?')) {
        $('#loadingOverlay').show();
        
        $.ajax({
            url: '{{ route("branch-inventory.add-product-to-branch") }}',
            method: 'POST',
            data: {
                product_id: productId,
                branch_id: branchId,
                quantity_available: 100,
                quantity_on_hand: 100,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showNotification('Product added to branch successfully with quantity 100!', 'success');
                setTimeout(() => window.location.reload(), 1500);
            },
            error: function(xhr) {
                let message = 'Error adding product to branch';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            },
            complete: function() {
                $('#loadingOverlay').hide();
            }
        });
    }
}

function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const notification = $(`
        <div class=\"alert ${alertClass} alert-dismissible fade show position-fixed notification-fixed\">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        notification.fadeOut(() => notification.remove());
    }, 3000);
}
</script>
@endpush