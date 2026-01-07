@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
<!-- Multi-Branch Inventory Bulk Operations CSS -->
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/bulk-operations.css') }}">
@endpush

@section('content')

<div class="modern-bulk-interface">
</style>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">
                    <i class="fas fa-boxes mr-2"></i>
                    Bulk Inventory Management
                </h1>
                <p class="mb-0 opacity-75">Edit inventory quantities, prices, and settings in bulk like default ecommerce</p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stats-card text-center">
                <div class="stats-value text-primary">{{ number_format($stats['total_products']) }}</div>
                <div class="stats-label">Total Products</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card text-center">
                <div class="stats-value text-warning">{{ number_format($stats['low_stock_items']) }}</div>
                <div class="stats-label">Low Stock</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card text-center">
                <div class="stats-value text-danger">{{ number_format($stats['out_of_stock']) }}</div>
                <div class="stats-label">Out of Stock</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card text-center">
                <div class="stats-value text-success">{{ number_format($stats['total_items'] ?? 0) }}</div>
                <div class="stats-label">Total Items</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card text-center">
                <div class="stats-value text-info">{{ number_format($stats['visible_online']) }}</div>
                <div class="stats-label">Online Visible</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card text-center">
                <div class="stats-value text-secondary">{{ number_format($stats['visible_in_pos']) }}</div>
                <div class="stats-label">POS Visible</div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="search-container">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Branch</label>
                <select name="branch_id" class="form-select form-control-modern" onchange="this.form.submit()">
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" 
                                {{ $selectedBranch && $selectedBranch->id == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Search Products</label>
                <input type="text" name="search" class="form-control form-control-modern" 
                       placeholder="Product name or SKU..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="low_stock_only" 
                           value="1" {{ request('low_stock_only') ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold">
                        Low Stock Only
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary-modern btn-modern me-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('branch-inventory.index') }}" class="btn btn-outline-secondary btn-modern">
                    <i class="fas fa-table"></i> Detailed View
                </a>
            </div>
        </form>
    </div>

    <!-- Bulk Inventory Table -->
    <div class="inventory-table">
        <div class="table-header">
            <h5 class="mb-0">
                <i class="fas fa-edit mr-2"></i>
                Bulk Edit Inventory - {{ $selectedBranch ? $selectedBranch->name : 'All Branches' }}
            </h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="col-image">Image</th>
                        <th>Product</th>
                        <th>Branch</th>
                        <th class="col-quantity">On Hand</th>
                        <th class="col-quantity">Available</th>
                        <th class="col-price">Cost Price</th>
                        <th class="col-price">Sell Price</th>
                        <th class="col-stock">Min Stock</th>
                        <th class="col-online">Online</th>
                        <th class="col-pos">POS</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryItems as $item)
                        @php
                            $product = $item->product;
                            $stockStatus = 'in-stock';
                            if ($item->quantity_available <= 0) {
                                $stockStatus = 'out-of-stock';
                            } elseif ($item->minimum_stock > 0 && $item->quantity_available <= $item->minimum_stock) {
                                $stockStatus = 'low-stock';
                            }
                        @endphp
                        <tr data-inventory-id="{{ $item->id }}">
                            <td>
                                <img src="{{ $product->image ? RvMedia::getImageUrl($product->image) : '/vendor/core/core/base/images/placeholder.png' }}" 
                                     alt="{{ $product->name }}" class="product-image">
                            </td>
                            <td>
                                <div class="fw-bold">{{ $product->name }}</div>
                                <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $product->sku }}</code>
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control editable-field bulk-editable" 
                                       data-field="quantity_on_hand"
                                       data-inventory-id="{{ $item->id }}"
                                       value="{{ $item->quantity_on_hand }}"
                                       min="0" step="1">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control editable-field bulk-editable" 
                                       data-field="quantity_available"
                                       data-inventory-id="{{ $item->id }}"
                                       value="{{ $item->quantity_available }}"
                                       min="0" step="1">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control editable-field bulk-editable" 
                                       data-field="cost_price"
                                       data-inventory-id="{{ $item->id }}"
                                       value="{{ $item->cost_price }}"
                                       min="0" step="0.01">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control editable-field bulk-editable" 
                                       data-field="selling_price"
                                       data-inventory-id="{{ $item->id }}"
                                       value="{{ $item->selling_price }}"
                                       min="0" step="0.01">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control editable-field bulk-editable" 
                                       data-field="minimum_stock"
                                       data-inventory-id="{{ $item->id }}"
                                       value="{{ $item->minimum_stock }}"
                                       min="0" step="1">
                            </td>
                            <td>
                                <select class="form-select editable-select bulk-editable" 
                                        data-field="visible_online"
                                        data-inventory-id="{{ $item->id }}">
                                    <option value="1" {{ $item->visible_online ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !$item->visible_online ? 'selected' : '' }}>No</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-select editable-select bulk-editable" 
                                        data-field="visible_in_pos"
                                        data-inventory-id="{{ $item->id }}">
                                    <option value="1" {{ $item->visible_in_pos ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !$item->visible_in_pos ? 'selected' : '' }}>No</option>
                                </select>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $stockStatus }}">
                                    @switch($stockStatus)
                                        @case('in-stock')
                                            <i class="fas fa-check-circle"></i> In Stock
                                            @break
                                        @case('low-stock')
                                            <i class="fas fa-exclamation-triangle"></i> Low Stock
                                            @break
                                        @case('out-of-stock')
                                            <i class="fas fa-times-circle"></i> Out of Stock
                                            @break
                                    @endswitch
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                <p>No inventory items found for the selected branch.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inventoryItems->hasPages())
            <div class="p-3 border-top">
                {{ $inventoryItems->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<script>
$(document).ready(function() {
    let timeout;
    
    // Auto-save on field change (like default ecommerce)
    $('.bulk-editable').on('input change', function() {
        clearTimeout(timeout);
        const $field = $(this);
        const inventoryId = $field.data('inventory-id');
        const fieldName = $field.data('field');
        const value = $field.val();
        const originalValue = $field.attr('data-original-value') || $field.val();
        
        // Store original value if not set
        if (!$field.attr('data-original-value')) {
            $field.attr('data-original-value', originalValue);
        }
        
        // Skip if value hasn't changed
        if (value === originalValue) {
            return;
        }
        
        // Add visual feedback
        $field.addClass('border-warning');
        
        // Auto-save after 1 second of no typing
        timeout = setTimeout(function() {
            updateInventoryField(inventoryId, fieldName, value, $field);
        }, 1000);
    });
    
    function updateInventoryField(inventoryId, fieldName, value, $field) {
        $('#loadingOverlay').show();
        
        $.ajax({
            url: '{{ route("branch-inventory.bulk-update") }}',
            method: 'POST',
            data: {
                inventory_id: inventoryId,
                field: fieldName,
                value: value,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update visual feedback
                    $field.removeClass('border-warning').addClass('border-success');
                    $field.attr('data-original-value', value);
                    
                    // Show success message briefly
                    showNotification('✓ Updated successfully', 'success');
                    
                    // Reset border after 2 seconds
                    setTimeout(function() {
                        $field.removeClass('border-success');
                    }, 2000);
                    
                } else {
                    showNotification(response.message || 'Update failed', 'error');
                    $field.addClass('border-danger');
                    
                    // Reset to original value
                    const originalValue = $field.attr('data-original-value');
                    if (originalValue) {
                        $field.val(originalValue);
                    }
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showNotification(response?.message || 'Update failed', 'error');
                $field.addClass('border-danger');
                
                // Reset to original value
                const originalValue = $field.attr('data-original-value');
                if (originalValue) {
                    $field.val(originalValue);
                }
            },
            complete: function() {
                $('#loadingOverlay').hide();
            }
        });
    }
    
    function showNotification(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const notification = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 class="notification-fixed">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').append(notification);
        
        // Auto-dismiss after 3 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 3000);
    }
});
</script>
@endsection